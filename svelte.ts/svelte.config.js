import adapter from '@sveltejs/adapter-static';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
  preprocess: vitePreprocess(),
  // for spa purpose
  kit: {
    adapter: adapter({
      pages: 'build',
      assets: 'build',
      fallback: '404.html', // Use 404.html for SPA compatibility on many hosts
      precompress: false,
      strict: true
    })
  }
};

export default config;


// import adapter from '@sveltejs/adapter-auto';
// import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

// /** @type {import('@sveltejs/kit').Config} */
// const config = {
// 	preprocess: vitePreprocess(),

// 	kit: {
// 		adapter: adapter()
// 	}
// };

// export default config;
