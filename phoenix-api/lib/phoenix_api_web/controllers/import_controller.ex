defmodule PhoenixApiWeb.ImportController do
  use PhoenixApiWeb, :controller

  alias PhoenixApi.Accounts

  def create(conn, _params) do
    required = System.get_env("IMPORT_TOKEN") || ""

    case get_req_header(conn, "x-api-token") do
      [^required] when required != "" ->
        {inserted, failed} = Accounts.import_random_users_from_pesel(100)
        json(conn, %{inserted: inserted, failed: failed})

      _ ->
        conn
        |> put_status(:unauthorized)
        |> json(%{error: "unauthorized"})
    end
  end
end
