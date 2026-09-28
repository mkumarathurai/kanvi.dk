import { readFile, writeFile, mkdir, copyFile } from 'node:fs/promises';
import { decompress } from 'wawoff2';
await mkdir('resources/fonts', { recursive: true });
await writeFile('resources/fonts/Inter.ttf', await decompress(await readFile('node_modules/@fontsource-variable/inter/files/inter-latin-wght-normal.woff2')));
await copyFile('node_modules/@fontsource-variable/inter/LICENSE', 'resources/fonts/OFL-Inter.txt');
