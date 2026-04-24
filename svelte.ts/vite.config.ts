// vite.config.ts
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';
import fs from 'fs';
import path from 'path';
import os from 'os';

// Use os.homedir() for better reliability across environments
const homeDir = os.homedir();
// Try .symfony5 first, then fallback to .symfony
let certPath = path.resolve(homeDir, '.symfony5/certs/default.p12');

if (!fs.existsSync(certPath)) {
    certPath = path.resolve(homeDir, '.symfony/certs/default.p12');
}

export default defineConfig({
    server: {
        https: fs.existsSync(certPath) ? {
            pfx: fs.readFileSync(certPath),
        } : undefined,
        host: '127.0.0.1', 
    },	
    plugins: [sveltekit()]
});



// import { sveltekit } from '@sveltejs/kit/vite';
// import { defineConfig } from 'vite';
// import fs from 'fs';
// import path from 'path';

// // Add a fallback to ensure homeDir is always a string
// const homeDir = process.env.HOME || process.env.USERPROFILE || ''; 
// const certPath = path.resolve(homeDir, '.symfony/certs/default.p12');

// export default defineConfig({
// server: {
//         https: {
//             // Using Symfony's p12 certificate directly
//             pfx: fs.readFileSync(certPath),
//         },
//         // Ensure this matches the host Symfony expects
//         host: '127.0.0.1', 
//     },	
// 	plugins: [sveltekit()]
// });
