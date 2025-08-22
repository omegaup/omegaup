import requests
import os

# GitHub repository details
GITHUB_REPO = "omegaup/omegaup"
GITHUB_TOKEN = os.getenv("GITHUB_TOKEN")  # Use GitHub Secret

headers = {
    "Authorization": f"token {GITHUB_TOKEN}",
    "Accept": "application/vnd.github.v3+json"
}

def get_pull_requests():
    url = f"https://api.github.com/repos/{GITHUB_REPO}/pulls"
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        prs = response.json()
        for pr in prs:
            print(f"PR #{pr['number']} - {pr['title']} by {pr['user']['login']}")
    else:
        print("Error fetching PRs:", response.json())

def merge_pr(pr_number):
    url = f"https://api.github.com/repos/{GITHUB_REPO}/pulls/{pr_number}/merge"
    data = {"commit_title": "Merging PR"}
    response = requests.put(url, headers=headers, json=data)

    if response.status_code == 200:
        print(f"PR #{pr_number} merged successfully!")
    else:
        print("Merge failed:", response.json())

if __name__ == "__main__":
    get_pull_requests()
    # merge_pr(1)  # Uncomment this to merge a specific PR
