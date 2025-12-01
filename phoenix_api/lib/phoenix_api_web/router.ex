defmodule PhoenixApiWeb.Router do
  use PhoenixApiWeb, :router

  pipeline :api do
    plug :accepts, ["json"]
  end

  pipeline :import_secure do
    plug PhoenixApiWeb.Plugs.ImportAuth
  end

  scope "/api", PhoenixApiWeb do
    pipe_through :api

    get "/users", UserController, :index
    get "/users/:id", UserController, :show
    post "/users", UserController, :create
    put "/users/:id", UserController, :update
    delete "/users/:id", UserController, :delete
  end

  scope "/api", PhoenixApiWeb do
    pipe_through [:api, :import_secure]

    post "/import", ImportController, :import
  end
end
