<?php
class AWeberCollection extends AWeberResponse implements ArrayAccess, Iterator, Countable {

	protected $pageSize  = 100;
	protected $pageStart = 0;

	protected function _updatePageSize() {

		// Grab the url, or prev and next url and pull ws.size from it.
		$url = $this->url;
		if (array_key_exists('next_collection_link', $this->data)) {
			$url = $this->data['next_collection_link'];

		} elseif (array_key_exists('prev_collection_link', $this->data)) {
			$url = $this->data['prev_collection_link'];
		}

		// Scan querystring for ws_size.
		$url_parts = parse_url($url);

		// We have a query string.
		if (array_key_exists('query', $url_parts)) {
			parse_str($url_parts['query'], $params);

			// We have a ws_size.
			if (array_key_exists('ws_size', $params)) {

				// Set pageSize.
				$this->pageSize = $params['ws_size'];
				return;
			}
		}

		// We dont have one, just count the # of entries.
		$this->pageSize = count($this->data['entries']);
	}

	public function __construct( $response, $url, $adapter) {
		parent::__construct($response, $url, $adapter);
		$this->_updatePageSize();
	}

	/**
	 * Holds list of keys that are not publicly accessible
	 *
	 * @var array
	 */
	protected $_privateData = array(
		'entries',
		'start',
		'next_collection_link',
	);

	/**
	 * GetById
	 *
	 * Gets an entry object of this collection type with the given id
	 *
	 * @param mixed $id     ID of the entry you are requesting
	 * @return AWeberEntry
	 */
	public function getById( $id) {
		$data = $this->adapter->request('GET', "{$this->url}/{$id}");
		$url  = "{$this->url}/{$id}";
		return new AWeberEntry($data, $url, $this->adapter);
	}

	/**
	 * Get Parent Entry
	 * Gets an entry's parent entry
	 * Returns NULL if no parent entry
	 */
	public function getParentEntry() {
		$url_parts = explode('/', $this->url);
		$size      = count($url_parts);

		// Remove collection id and slash from end of url.
		$url = substr($this->url, 0, -strlen($url_parts[$size-1])-1);

		try {
			$data = $this->adapter->request('GET', $url);
			return new AWeberEntry($data, $url, $this->adapter);
		} catch (Exception $e) {
			return null;
		}
	}

	/**
	 * _type
	 *
	 * Interpret what type of resources are held in this collection by 
	 * analyzing the URL
	 *
	 * @return void
	 */
	protected function _type() {
		$urlParts = explode('/', $this->url);
		$type     = array_pop($urlParts);
		return $type;
	}

	/**
	 * Create
	 *
	 * Invoke the API method to CREATE a new entry resource.
	 *
	 * Note: Not all entry resources are eligible to be created, please
	 *       refer to the AWeber API Reference Documentation at
	 *       https://labs.aweber.com/docs/reference/1.0 for more
	 *       details on which entry resources may be created and what
	 *       attributes are required for creating resources.
	 *
	 * @param params mixed  associtative array of key/value pairs.
	 * @return AWeberEntry(Resource) The new resource created
	 */
	public function create( $kv_pairs) {
		// Create Resource.
		$params = array_merge(array('ws.op' => 'create'), $kv_pairs);
		$data   = $this->adapter->request('POST', $this->url, $params, array('return' => 'headers'));

		// Return new Resource.
		$url           = $data['Location'];
		$resource_data = $this->adapter->request('GET', $url);
		return new AWeberEntry($resource_data, $url, $this->adapter);
	}

	/**
	 * Find
	 *
	 * Invoke the API 'find' operation on a collection to return a subset
	 * of that collection.  Not all collections support the 'find' operation.
	 * refer to https://labs.aweber.com/docs/reference/1.0 for more information.
	 *
	 * @param mixed $search_data   Associative array of key/value pairs used as search filters
	 *                             * refer to https://labs.aweber.com/docs/reference/1.0 for a
	 *                               complete list of valid search filters.
	 *                             * filtering on attributes that require additional permissions to
	 *                               display requires an app authorized with those additional permissions.
	 * @return AWeberCollection 
	 */
	public function find( $search_data) {
		// Invoke find operation.
		$params = array_merge($search_data, array('ws.op' => 'find'));
		$data   = $this->adapter->request('GET', $this->url, $params);

		// Get total size.
		$ts_params          = array_merge($params, array('ws.show' => 'total_size'));
		$total_size         = $this->adapter->request('GET', $this->url, $ts_params, array('return' => 'integer'));
		$data['total_size'] = $total_size;

		// Return collection.
		return $this->readResponse($data, $this->url);
	}

	/*
	 * ArrayAccess Functions
	 *
	 * Allows this object to be accessed via bracket notation (ie $obj[$x])
	 * http://php.net/manual/en/class.arrayaccess.php
	 */

	public function offsetSet( $offset, $value)  {}
	public function offsetUnset( $offset)        {}
	public function offsetExists( $offset) {

		if ($offset >=0 && $offset < $this->total_size) {
			return true;
		}
		return false;
	}
	protected function _fetchCollectionData( $offset) {

		// We dont have a next page, we're done.
		if (!array_key_exists('next_collection_link', $this->data)) {
			return null;
		}

		// Snag query string args from collection.
		$parsed = parse_url($this->data['next_collection_link']);

		// Parse the query string to get params.
		$pairs = explode('&', $parsed['query']);
		foreach ($pairs as $pair) {
			list($key, $val) = explode('=', $pair);
			$params[$key]    = $val;
		}

		// Calculate new args.
		$limit              = $params['ws.size'];
		$pagination_offset  = intval($offset / $limit) * $limit;
		$params['ws.start'] = $pagination_offset;

		// Fetch data, exclude query string.
		$url_parts       = explode('?', $this->url);
		$data            = $this->adapter->request('GET', $url_parts[0], $params);
		$this->pageStart = $params['ws.start'];
		$this->pageSize  = $params['ws.size'];

		$collection_data = array('entries', 'next_collection_link', 'prev_collection_link', 'ws.start');

		foreach ($collection_data as $item) {
			if (!array_key_exists($item, $this->data)) {
				continue;
			}
			if (!array_key_exists($item, $data)) {
				continue;
			}
			$this->data[$item] = $data[$item];
		}
	}

	public function offsetGet( $offset) {

		if (!$this->offsetExists($offset)) {
			return null;
		}

		$limit             = $this->pageSize;
		$pagination_offset = intval($offset / $limit) * $limit;

		// Load collection page if needed.
		if ($pagination_offset !== $this->pageStart) {
			$this->_fetchCollectionData($offset);
		}

		$entry = $this->data['entries'][$offset - $pagination_offset];

		// We have an entry, cast it to an AWeberEntry and return it.
		$entry_url = $this->adapter->app->removeBaseUri($entry['self_link']);
		return new AWeberEntry($entry, $entry_url, $this->adapter);
	}

	/*
	 * Iterator
	 */
	protected $_iterationKey = 0;

	public function current() {
		return $this->offsetGet($this->_iterationKey);
	}

	public function key() {
		return $this->_iterationKey;
	}

	public function next() {
		$this->_iterationKey++;
	}

	public function rewind() {
		$this->_iterationKey = 0;
	}

	public function valid() {
		return $this->offsetExists($this->key());
	}

	/*
	 * Countable interface methods
	 * Allows PHP's count() and sizeOf() functions to act on this object
	 * http://www.php.net/manual/en/class.countable.php
	 */

	public function count() {
		return $this->total_size;
	}
}
