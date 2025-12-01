import Config

# Configure your database
config :phoenix_api, PhoenixApi.Repo,
  username: "postgres",
  password: "postgres",
  hostname: "db",
  database: "phoenix_app",
  show_sensitive_data_on_connection_error: true,
  pool_size: 10

# Import token (separate config!)
config :phoenix_api,
  import_token: "SECRET123"

config :phoenix_api, PhoenixApiWeb.Endpoint,
  http: [ip: {0, 0, 0, 0}],
  check_origin: false,
  code_reloader: true,
  debug_errors: true,
  secret_key_base: "FtZhHJGe8Y2VieBQiQWaXoU8AfB+1GGk+i6Ie3PD2PsLBuddJKufos71m/LtoiVP",
  watchers: []

config :phoenix_api, dev_routes: true

config :logger, :default_formatter, format: "[$level] $message\n"

config :phoenix, :stacktrace_depth, 20

config :phoenix, :plug_init_mode, :runtime

config :swoosh, :api_client, false
