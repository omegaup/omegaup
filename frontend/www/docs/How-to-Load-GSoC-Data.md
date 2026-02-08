# How to Load Past GSoC Data

This guide explains how to populate the GSoC database with past editions and ideas.

## Method 1: Using SQL Scripts (Recommended for Bulk Import)

### Step 1: Create Editions

First, insert GSoC editions (one per year):

```sql
INSERT INTO `GSoC_Edition` (`year`, `is_active`, `application_deadline`)
VALUES
  (2025, 1, '2025-03-18 23:59:59'),  -- Set is_active = 1 for current edition
  (2024, 0, '2024-03-18 23:59:59'),
  (2023, 0, '2023-03-20 23:59:59'),
  (2022, 0, '2022-03-20 23:59:59');
```

**Important**: Only ONE edition should have `is_active = 1` (the current/active edition).

### Step 2: Find Edition IDs

After inserting editions, find their IDs:

```sql
SELECT edition_id, year, is_active FROM GSoC_Edition ORDER BY year DESC;
```

### Step 3: Insert Ideas

Insert ideas linked to editions using the `edition_id`:

```sql
INSERT INTO `GSoC_Idea` (
  `edition_id`,
  `title`,
  `brief_description`,
  `expected_results`,
  `preferred_skills`,
  `possible_mentors`,
  `estimated_hours`,
  `skill_level`,
  `status`,
  `blog_link`,
  `contributor_username`
)
VALUES
  (
    1,  -- Replace with actual edition_id
    'Project Title',
    'Brief description of the project',
    'What we expect to achieve',
    'PHP, MySQL, JavaScript, Vue.js',
    'mentor1, mentor2',
    175,
    'Medium',  -- Options: 'Low', 'Medium', 'Advanced'
    'Accepted',  -- Options: 'Proposed', 'Accepted', 'Archived'
    'https://blog.omegaup.com/gsoc-2025-idea1',
    NULL  -- Username of contributor if assigned
  );
```

### Required Fields:
- **`edition_id`**: Must reference an existing edition
- **`title`**: Required, cannot be empty
- **`status`**: One of: 'Proposed', 'Accepted', 'Archived' (default: 'Proposed')
- **`skill_level`**: One of: 'Low', 'Medium', 'Advanced' (optional)

### Optional Fields:
- `brief_description`
- `expected_results`
- `preferred_skills`
- `possible_mentors`
- `estimated_hours` (integer)
- `blog_link` (valid URL)
- `contributor_username` (valid username)

## Method 2: Using the Admin Panel (UI)

1. Access the admin panel at `/admin/gsoc` (needs to be created)
2. Go to the "Editions" tab
3. Create editions using the form
4. Go to the "Ideas" tab
5. Create ideas and link them to editions

## Method 3: Using API Calls

You can use the API endpoints directly:

### Create Edition:
```bash
POST /api/gSoC/createEdition/
{
  "year": 2025,
  "is_active": true,
  "application_deadline": "2025-03-18 23:59:59"
}
```

### Create Idea:
```bash
POST /api/gSoC/createIdea/
{
  "edition_id": 1,
  "title": "Project Title",
  "brief_description": "Description",
  "expected_results": "Results",
  "preferred_skills": "PHP, MySQL",
  "possible_mentors": "mentor1",
  "estimated_hours": 175,
  "skill_level": "Medium",
  "status": "Accepted"
}
```

## Example: Loading Data from Markdown Files

If you have past GSoC data in markdown files (like `Google-Summer-of-Code-2025.md`), you can:

1. Parse the markdown to extract ideas
2. Create the edition for that year
3. Insert each idea with the corresponding `edition_id`

## Quick Reference

**To set an edition as active:**
```sql
UPDATE GSoC_Edition SET is_active = 0;  -- Deactivate all
UPDATE GSoC_Edition SET is_active = 1 WHERE year = 2025;  -- Activate current
```

**To check what's in the database:**
```sql
-- List all editions
SELECT * FROM GSoC_Edition ORDER BY year DESC;

-- List all ideas
SELECT idea_id, edition_id, title, status FROM GSoC_Idea ORDER BY created_at DESC;

-- Count ideas per edition
SELECT e.year, COUNT(i.idea_id) as idea_count
FROM GSoC_Edition e
LEFT JOIN GSoC_Idea i ON e.edition_id = i.edition_id
GROUP BY e.year
ORDER BY e.year DESC;
```
