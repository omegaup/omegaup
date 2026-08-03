import requests
import os

# omegaUp API details
OMEGAUP_API_URL = "https://omegaup.com/api/course/details/"
OMEGAUP_TOKEN = os.getenv("OMEGAUP_TOKEN")  # Use GitHub Secret

headers = {"Authorization": f"Bearer {OMEGAUP_TOKEN}"}

def fetch_courses():
    response = requests.get(OMEGAUP_API_URL, headers=headers)

    if response.status_code == 200:
        return response.json().get("courses", [])
    else:
        print("Error fetching courses:", response.json())
        return []

def save_course_to_file(course):
    filename = f"courses/{course['alias']}.md"
    with open(filename, "w", encoding="utf-8") as f:
        f.write(f"# {course['name']}\n\n")
        f.write(course.get("description", "No description available."))
    print(f"✅ Saved: {filename}")

def update_courses():
    courses = fetch_courses()
    if not courses:
        print("No courses found.")
        return

    os.makedirs("courses", exist_ok=True)
    for course in courses:
        save_course_to_file(course)

if __name__ == "__main__":
    update_courses()
