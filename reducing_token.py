import os
import sys

import openai
import requests
from bs4 import BeautifulSoup
from requests.exceptions import RequestException
from openai.error import OpenAIError


def get_api_key() -> str:
    api_key = os.getenv("OPENAI_API_KEY")
    if not api_key:
        print("Error: OPENAI_API_KEY environment variable is not set.")
        sys.exit(1)
    return api_key


def fetch_article_content(target_url: str) -> str:
    try:
        response = requests.get(target_url, timeout=10)
        response.raise_for_status()
    except RequestException as exc:
        print(f"Error fetching content from {target_url}: {exc}")
        sys.exit(1)

    soup = BeautifulSoup(response.text, "html.parser")
    article = soup.find("article")
    if article is None:
        print("Error: Unable to locate article content on the page.")
        sys.exit(1)
    return article.get_text()


def generate_summary(content: str) -> str:
    if not getattr(openai, "api_key", None):
        openai.api_key = get_api_key()
    try:
        response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "user", "content": f"Summarize this: {content[:1000]}"}
            ],
        )
    except OpenAIError as exc:  # Catch OpenAI request issues without crashing
        print(f"Error generating summary with OpenAI: {exc}")
        sys.exit(1)

    return response["choices"][0]["message"]["content"]


if __name__ == "__main__":
    url = "https://example.com"  # From QR code
    if not getattr(openai, "api_key", None):
        openai.api_key = get_api_key()
    main_content = fetch_article_content(url)
    summary = generate_summary(main_content)
    print(summary)
