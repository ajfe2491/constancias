import mysql from 'mysql2/promise';
import mjml from 'mjml';

const config = {
  host: process.env.DB_HOST || '127.0.0.1',
  port: Number(process.env.DB_PORT || 3307),
  user: process.env.DB_USERNAME || 'constancias',
  password: process.env.DB_PASSWORD || 'constancias',
  database: process.env.DB_DATABASE || 'constancias',
};

const extractMjml = (html) => {
  if (!html) return null;
  const match = html.match(/<mjml[\s\S]*<\/mjml>/i);
  return match ? match[0] : null;
};

const convertRow = async (conn, table, id, html) => {
  const mjmlSource = extractMjml(html);
  if (!mjmlSource) return false;

  const { html: compiled, errors } = mjml(mjmlSource, { minify: true });
  if (!compiled || (errors && errors.length)) {
    console.warn(`[WARN] ${table}#${id} conversion errors:`, errors?.length || 0);
  }

  await conn.execute(
    `UPDATE \`${table}\`
     SET email_template_html = ?, email_template_mjml = ?
     WHERE id = ?`,
    [compiled || html, mjmlSource, id]
  );

  return true;
};

const run = async () => {
  const conn = await mysql.createConnection(config);
  const tables = ['events', 'document_configurations'];
  let updated = 0;

  for (const table of tables) {
    const [rows] = await conn.execute(
      `SELECT id, email_template_html
       FROM \`${table}\`
       WHERE email_template_html LIKE '%<mjml%'`
    );

    for (const row of rows) {
      if (await convertRow(conn, table, row.id, row.email_template_html)) {
        updated += 1;
      }
    }
  }

  await conn.end();
  console.log(`Converted templates: ${updated}`);
};

run().catch((err) => {
  console.error(err);
  process.exit(1);
});
