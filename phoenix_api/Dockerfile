FROM elixir:1.16-alpine

RUN apk add --no-cache git build-base curl
RUN apk add --update nodejs npm

WORKDIR /phoenix_api

RUN mix local.hex --force && mix local.rebar --force
RUN mix archive.install hex phx_new --force

EXPOSE 4000

CMD ["mix", "phx.server"]
