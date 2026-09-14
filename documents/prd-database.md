
[user6112@juwana public_html]$ tail -50 /home/user6112/.npm/_logs/2026-09-14T07_21_06_533Z-debug-0.log1160 verbose stack     at new Promise (<anonymous>)
1160 verbose stack     at callLimit (/opt/alt/alt-nodejs20/root/usr/lib/node_modules/npm/node_modules.bundled/promise-call-limit/dist/commonjs/index.js:35:69)
1161 verbose pkgid esbuild@0.24.2
1162 error code 1
1163 error path /home/user6112/public_html/node_modules/esbuild
1164 error command failed1165 error command sh -c node install.js
1166 error node:internal/child_process:11231166 error     result.error = new ErrnoException(result.error, 'spawnSync ' + options.file);
1166 error                    ^
1166 error
1166 error <ref *1> Error: spawnSync /home/user6112/public_html/node_modules/esbuild/bin/esbuild EAGAIN1166 error     at Object.spawnSync (node:internal/child_process:1123:20)
1166 error     at spawnSync (node:child_process:877:24)
1166 error     at Object.execFileSync (node:child_process:920:15)
1166 error     at validateBinaryVersion (/home/user6112/public_html/node_modules/esbuild/install.js:101:28)
1166 error     at /home/user6112/public_html/node_modules/esbuild/install.js:285:5 {
1166 error   errno: -11,
1166 error   code: 'EAGAIN',
1166 error   syscall: 'spawnSync /home/user6112/public_html/node_modules/esbuild/bin/esbuild',
1166 error   path: '/home/user6112/public_html/node_modules/esbuild/bin/esbuild',
1166 error   spawnargs: [ '--version' ],
1166 error   error: [Circular *1],
1166 error   status: null,
1166 error   signal: null,
1166 error   output: null,
1166 error   pid: 0,
1166 error   stdout: null,
1166 error   stderr: null
1166 error }
1166 error
1166 error Node.js v20.20.2
1167 silly unfinished npm timer reify 1789370467786
1168 silly unfinished npm timer reify:build 1789370497652
1169 silly unfinished npm timer build 1789370497656
1170 silly unfinished npm timer build:deps 1789370497658
1171 silly unfinished npm timer build:run:postinstall 1789370497776
1172 silly unfinished npm timer build:run:postinstall:node_modules/esbuild 1789370497777
1173 verbose cwd /home/user6112/public_html
1174 verbose os Linux 5.14.0-687.24.1.el9_8.x86_64
1175 verbose node v20.20.2
1176 verbose npm  v10.8.2
1177 notice
1177 notice New major version of npm available! 10.8.2 -> 12.0.2
1177 notice Changelog: https://github.com/npm/cli/releases/tag/v12.0.2
1177 notice To update run: npm install -g npm@12.0.2
1177 notice  { force: true, [Symbol(proc-log.meta)]: true }
1178 verbose exit 1
1179 verbose code 1
1180 error A complete log of this run can be found in: /home/user6112/.npm/_logs/2026-09-14T07_21_06_533Z-debug-0.log
[user6112@juwana public_html]$