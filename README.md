## Morizon–Gratka Recruitment Task


Symfony uses a DDD-inspired modular structure (Application / Infrastructure / UI),
while the domain logic and persistence live in the Phoenix API.
### Requirements
Docker + Docker Compose

### Configuration

Symfony requires APP_SECRET.

Generate it locally:
```bash
php -r 'echo bin2hex(random_bytes(16)).PHP_EOL;'
```

paste generated value in ```.env.example```
```dotenv
APP_SECRET=generated_value_here
```

then rename ```.env.example``` to ```.env.local```

### Run project

1. Go to the project root (``` /morizon-gratka```), build and start containers:
```bash
docker compose up -d --build
```

2. Run database migrations:
```bash
docker compose exec phoenix mix ecto.migrate
```

3. Install Symfony dependencies:
```bash
docker compose exec symfony composer install
```

### After startup

Phoenix API → http://localhost:4000

Symfony frontend → http://localhost:8000/users

## Import initial data (Phoenix)

Phoenix exposes an import endpoint that generates 100 users based on PESEL name datasets.

Import adds users; for clean demo use TRUNCATE command at the end of this readme.

Import can be executed multiple times (for demo purposes)

### Security
Endpoint is protected with x-api-token header.

Token is configured via ```IMPORT_TOKEN``` env var in ``docker-compose.yml`` (phoenix service). 

If you change it, remember to rebuild the containers.

### Run:
```bash
curl -X POST http://localhost:4000/api/import -H "x-api-token: change_me"
```

## Data sources

User data is generated from official PESEL datasets:

[Top 100 female first names](https://dane.gov.pl/pl/dataset/1667,lista-imion-wystepujacych-w-rejestrze-pesel-osoby-zyjace/resource/63924/table?page=1&per_page=20&q=&sort=)

[Top 100 male first names](https://dane.gov.pl/pl/dataset/1667,lista-imion-wystepujacych-w-rejestrze-pesel-osoby-zyjace/resource/63929/table?page=1&per_page=20&q=&sort=)

[Top 100 female last names](https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel/resource/63888/table?page=1&per_page=20&q=&sort=)

[Top 100 male last names](https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel/resource/63892/table?page=1&per_page=20&q=&sort=)

CSV files are stored locally in ```phoenix-api/priv/pesel/```

### Generation rules:

- Random first name + last name
- Gender consistent with first name
- Birthdate randomly generated between 1970-01-01 and 2024-12-31

## Endpoints
### Phoenix - base URL: http://localhost:4000
| Method	 | Endpoint       | 	Description                              |
|---------|----------------|-------------------------------------------|
| GET	    | /api/users     | 	List users (filters & sorting supported) |
| POST	   | /api/users     | 	Create user                              |
| GET	    | /api/users/:id | 	Get user details                         |
| PUT	    | /api/users/:id | 	Update user                              |
| DELETE	 | /api/users/:id | 	Delete user                              |
| POST	   | /api/import    | 	Import users (requires x-api-token)      |

### Symfony base URL: http://localhost:8000

| Route              | 	Description     |
|--------------------|------------------|
| /users             | Users list       |
| /users/new         | Create user form |
| /users/{id}/edit   | Edit form        |
| /users/{id}/delete | Delete           |

### Features:

- User list
- Filtering & sorting
- Create / edit / delete users
- Communication with Phoenix API via HttpClient
- DDD-styled structure
- Commands & Queries handled via Symfony Messenger (sync)

### Useful commands
to clear users data in Phoenix DB:
```bash
docker compose exec db psql -U morizon -d morizon -c "TRUNCATE users RESTART IDENTITY;"
```

### Curl examples

#### List users
```bash
curl -i http://localhost:4000/api/users
```

#### Filtering
```bash
curl -i "http://localhost:4000/api/users?first_name=ANNA"
curl -i "http://localhost:4000/api/users?last_name=NOWAK"
curl -i "http://localhost:4000/api/users?gender=female"
```

#### Date range filter
```bash
curl -i "http://localhost:4000/api/users?birthdate_from=1980-01-01&birthdate_to=1990-12-31"
```

#### Sorting
```bash
curl -i "http://localhost:4000/api/users?sort_by=last_name&sort_dir=asc"
curl -i "http://localhost:4000/api/users?sort_by=birthdate&sort_dir=desc"
```

#### Create user
```bash
curl -i -X POST http://localhost:4000/api/users \
    -H "Content-Type: application/json" \
    -d '{
        "user": {
            "first_name": "Jan",
            "last_name": "Kowalski",
            "gender": "male",
            "birthdate": "1985-06-15"
        }
    }'
```

#### Get user details
```bash
curl -i http://localhost:4000/api/users/1
```

#### User not found (404 example)
```bash
curl -i http://localhost:4000/api/users/999999
```

#### Update user
```bash
curl -i -X PUT http://localhost:4000/api/users/1 \
    -H "Content-Type: application/json" \
    -d '{
        "user": {
          "last_name": "Nowak"
        }
    }'
```

#### Delete user
```bash
curl -i -X DELETE http://localhost:4000/api/users/1
```

#### Import users (token protected)
```bash
curl -i -X POST http://localhost:4000/api/import \
-H "x-api-token: change_me"
```

#### Import without token (expected 401)
```bash 
curl -i -X POST http://localhost:4000/api/import
```