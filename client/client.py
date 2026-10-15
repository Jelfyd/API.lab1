"""Консольний клієнт для локального Space API."""

import argparse
import json
from urllib.error import HTTPError, URLError
from urllib.request import urlopen


def fetch_json(url: str) -> dict:
    with urlopen(url, timeout=5) as response:
        return json.loads(response.read().decode("utf-8"))


def main() -> int:
    parser = argparse.ArgumentParser(description="Отримати дані з локального Space API")
    parser.add_argument("--base-url", default="http://127.0.0.1:8000", help="Адреса API")
    parser.add_argument("--id", type=int, help="Показати один об’єкт за його ID")
    args = parser.parse_args()

    endpoint = f"{args.base_url.rstrip('/')}/api/space"
    if args.id is not None:
        endpoint += f"/{args.id}"

    try:
        result = fetch_json(endpoint)
    except HTTPError as error:
        if error.code == 404:
            print(f"Об’єкт з ID {args.id} не знайдено.")
            return 1
        print(f"HTTP-помилка {error.code}: {error.reason}")
        return 1
    except (URLError, TimeoutError) as error:
        print(f"Не вдалося під’єднатися до API: {error}")
        print("Переконайтеся, що PHP-сервер запущений на вказаній адресі.")
        return 1
    except json.JSONDecodeError:
        print("API повернув некоректний JSON.")
        return 1

    items = result.get("data")
    if isinstance(items, dict):
        items = [items]

    for item in items or []:
        print(f"[{item['id']}] {item['name']} ({item['type']})")
        print(f"  {item['description']}")
        print(f"  Цікавий факт: {item['fun_fact']}")
        print()

    return 0


if __name__ == "__main__":
    raise SystemExit(main())