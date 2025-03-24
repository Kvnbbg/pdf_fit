import requests
from bs4 import BeautifulSoup

# Fetch and preprocess webpage content
url = "https://example.com"  # From QR code
response = requests.get(url)
soup = BeautifulSoup(response.text, "html.parser")
main_content = soup.find("article").get_text()  # Extract main article text

# Summarize with OpenAI
summary = openai.ChatCompletion.create(
    model="gpt-3.5-turbo",
    messages=[{"role": "user", "content": f"Summarize this: {main_content[:1000]}"}]  # Limit to 1000 chars
)
