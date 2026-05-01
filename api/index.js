const serverlesswp = require('serverlesswp');

const { validate } = require('../util/install.js');
const { setup } = require('../util/directory.js');
const sqliteS3 = require('../util/sqliteS3.js');
const sandbox = require('../util/sandbox.js');
const readOnly = require('../util/readOnly.js');

const pathToWP = '/tmp/wp';
let initSqliteS3 = false;

// Move the /wp directory to /tmp/wp so that it is writeable.
setup();

if (process.env['SERVERLESSWP_READ_ONLY_MODE'] && !['false', '0', 'no'].includes(process.env['SERVERLESSWP_READ_ONLY_MODE'].toLowerCase())) {
  serverlesswp.registerPlugin(readOnly);
}

if (process.env['SQLITE_S3_BUCKET'] || process.env['SERVERLESSWP_DATA_SECRET']) {
  serverlesswp.registerPlugin(sqliteS3);
}

if (process.env['SERVERLESSWP_DATA_SECRET']) {
  serverlesswp.registerPlugin(sandbox);
}

// ✅ Format Vercel: (req, res) — bukan exports.handler
module.exports = async (req, res) => {
  if ((process.env['SQLITE_S3_BUCKET'] || process.env['SERVERLESSWP_DATA_SECRET']) && !initSqliteS3) {
    let wpContentPath = pathToWP + '/wp-content';
    let sqlitePluginPath = wpContentPath + '/plugins/sqlite-database-integration';
    await sqliteS3.prepPlugin(wpContentPath, sqlitePluginPath);

    let branchSlug = '';
    let bucketFallback = '';

    if (process.env['VERCEL']) {
      const branch = sqliteS3.branchNameToS3file(process.env['VERCEL_GIT_COMMIT_REF']);
      branchSlug = branch ? '-' + branch : '';
      bucketFallback = process.env['VERCEL_PROJECT_ID'];
    }

    let sqliteS3Config = {
      bucket: process.env['SQLITE_S3_BUCKET'] || bucketFallback,
      file: `wp-sqlite-s3${branchSlug}.sqlite`,
      S3Client: {
        credentials: {
          accessKeyId: process.env['SQLITE_S3_API_KEY'] || process.env['VERCEL_PROJECT_ID'],
          secretAccessKey: process.env['SQLITE_S3_API_SECRET'] || process.env['SERVERLESSWP_DATA_SECRET'],
        },
        region: process.env['SQLITE_S3_REGION'],
      },
    };

    if (process.env['SQLITE_S3_ENDPOINT']) {
      sqliteS3Config.S3Client.endpoint = process.env['SQLITE_S3_ENDPOINT'];
    }

    if (process.env['SQLITE_S3_FORCE_PATH_STYLE'] || process.env['SERVERLESSWP_DATA_SECRET']) {
      sqliteS3Config.S3Client.forcePathStyle = true;
    }

    if (process.env['SERVERLESSWP_DATA_SECRET']) {
      sqliteS3Config.S3Client.endpoint = 'https://data.serverlesswp.com';
      sqliteS3Config.onAuthError = () => sandbox.register(sqliteS3Config.bucket, process.env['SERVERLESSWP_DATA_SECRET']);
    }

    sqliteS3.config(sqliteS3Config);
    initSqliteS3 = true;
  }

  // ✅ Konversi req ke format event yang dimengerti serverlesswp
  const event = {
    httpMethod: req.method,
    path: req.url,
    headers: req.headers,
    queryStringParameters: req.query,
    body: await getRawBody(req),
    isBase64Encoded: false,
  };

  let response = await serverlesswp({ docRoot: pathToWP, event });

  let checkInstall = validate(response);
  const result = checkInstall || response;

  // ✅ Kirim response dalam format Vercel (res.status().send())
  res.status(result.statusCode || 200);

  if (result.headers) {
    Object.entries(result.headers).forEach(([key, value]) => {
      res.setHeader(key, value);
    });
  }

  const body = result.isBase64Encoded ? Buffer.from(result.body, 'base64') : result.body;

  res.send(body);
};

// Helper: baca raw body dari request stream
async function getRawBody(req) {
  return new Promise((resolve, reject) => {
    let data = '';
    req.on('data', (chunk) => (data += chunk));
    req.on('end', () => resolve(data));
    req.on('error', reject);
  });
}
