FROM elixir:1.16.3

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential git curl \
 && rm -rf /var/lib/apt/lists/*

RUN mix local.hex --force && mix local.rebar --force

# deps cache
COPY mix.exs mix.lock ./
RUN mix deps.get

COPY . .

EXPOSE 4000
CMD ["bash", "-lc", "mix deps.get && mix ecto.migrate && mix phx.server"]