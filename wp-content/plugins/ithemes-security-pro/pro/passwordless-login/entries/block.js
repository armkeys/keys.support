/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './block/edit';
import metadata from './block/block.json';

registerBlockType( metadata.name, {
	icon: {
		foreground: '#0083E3',
		src: <svg viewBox="0 0 102.43 113.98" xmlns="http://www.w3.org/2000/svg"><g><path d="M102.41 40.58V8h-.001c0-.95-.77-1.73-1.71-1.74 0 0-37.21-6.04-49.62-6.26 -12.41-.22-49.3 6-49.3 6v0C.8 5.97.01 6.73-.01 7.69c-.01.01-.01.02-.01.04v32.84s-1.16 27.36 17.44 48.9c16.52 19.13 30.32 23.66 33.2 24.44v0c.36.07.74.07 1.11 0 2.92-.79 16.7-5.35 33.18-24.44 18.6-21.54 17.48-48.9 17.48-48.9Zm-25 43c-11.21 13-20.79 18.57-25.67 20.8v0c-.37.13-.78.13-1.14 0 -4.84-2.29-14.43-7.88-25.6-20.85C8.49 64.45 9.15 39.45 9.16 39.19v-25 0c.01-.37.25-.68.6-.78l0-.001c13.46-3.41 27.28-5.15 41.17-5.2h.71l0 0c13.82 0 27.6 1.74 41 5.18l-.001-.001c.34.1.58.41.6.78v25.06c.03.22.68 25.22-15.78 44.3Z" /><path d="M51.1 31.87v0c-4.9-.02-8.88 3.93-8.89 8.82 -.02 4.89 3.93 8.87 8.82 8.88 4.89.01 8.87-3.94 8.88-8.83 0-.01 0-.01 0-.01l-.001-.001c0-4.89-3.95-8.86-8.83-8.88Z" /><path d="M84.1 39.41v-18 0c-.02-.35-.26-.65-.59-.73v-.001c-10.48-2.19-21.16-3.28-31.86-3.28H51h-.001c-10.76.05-21.47 1.17-32 3.34v0c-.34.08-.58.38-.59.73v18.07c0 1.33 0 22.31 13.59 38 7.53 8.82 14.14 13.76 18.7 16.46h-.001c.31.15.68.15 1 0 4.53-2.69 11.14-7.63 18.77-16.46 13.94-16.11 13.63-37.88 13.63-38.13ZM63.51 71.6c-1.09 1.28-2.16 2.45-3.2 3.54 -.24.25-.43.17-.43-.18V67.4c0-5.72 5.55-8.63 7.38-9.43v0c.33-.16.56-.49.58-.86v-5.74h0c.03-.21-.11-.41-.32-.44 -.1-.02-.19 0-.27.05l-.001 0c-10.4 4.1-21.97 4.1-32.36.01v0c-.19-.11-.43-.05-.54.14 -.04.07-.06.15-.05.23v5.74 0c.02.36.25.69.59.84 6.08 2.33 7.32 6.29 7.32 9.52v7.42c0 .35-.19.43-.43.18q-1.53-1.6-3.16-3.48c-11.44-13.26-11.37-31.84-11.37-32v-10.6 0c.01-.36.27-.66.62-.72h0c7.6-1.2 15.29-1.81 23-1.83h.69v-.001c7.59 0 15.18.58 22.69 1.76v0c.35.06.61.36.62.72v10.3c-.001.35.11 19.08-11.38 32.37Z" /></g></svg> },
	edit,
} );