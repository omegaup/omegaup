# GitHub Guidelines



## 📝 Commit Guidelines

### Subject Line Requirements
- **Cannot be empty** - All commits must have a subject line
- **Length**: Must be between 2-999 characters
- **Format**: Must be a single line (no line breaks)
- **Punctuation**: Must NOT end with a period (.)

### Body Requirements
- **Line length**: All body lines must be shorter than 1000 characters

### Examples

✅ **Good commit subjects:**
```
Add user authentication system
Fix navbar responsive design issues
Update dependencies to latest versions
Refactor database connection logic
```

❌ **Bad commit subjects:**
```
# Empty subject (not allowed)

A# Too short (only 1 character)

Add user authentication system.  # Ends with period (not allowed)

This is a very long commit message that exceeds the maximum allowed length and goes on and on about various changes made to the codebase without being concise and to the point which makes it harder to read and understand quickly  # Too long (over 1000 characters)
```

## 🔄 Pull Request Guidelines

### Title Requirements
- **Cannot be empty** - All PRs must have a title
- **Length**: Must be between 5-999 characters
- **Punctuation**: CAN end with a period (optional)

### Body Requirements
- **Cannot be empty** - All PRs must have a description
- **GitHub Issue**: Must reference a related GitHub issue
- **Screenshots**: Required for changes involving:
  - HTML files
  - CSS files
  - Vue files
  - Template (.tpl) files

### Pull Request Template

```markdown
## Description
Brief description of the changes made.

## Related Issue
Fixes #[issue-number]

## Changes Made
- List of changes
- Another change
- Final change

## Screenshots (if applicable)
<!-- Required for HTML, CSS, Vue, and .tpl file changes -->
[Add screenshots here]

## Additional Notes
Any additional context or information.
```

### Examples

✅ **Good PR titles:**
```
Implement user dashboard with responsive design
Fix authentication bug in login form
Add dark mode toggle functionality
Update styling for mobile navigation menu
```

❌ **Bad PR titles:**
```
Fix   # Too short (less than 5 characters)
#     # Empty title (not allowed)
```

## 🐛 Issue Guidelines

### Title Requirements
- **Cannot be empty** - All issues must have a title
- **Length**: Must be between 2-999 characters
- **Punctuation**: CAN end with a period (optional)

### Body Requirements
- **Cannot be empty** - All issues must have a description

### Issue Template

```markdown
## Issue Description
Clear and concise description of the problem or feature request.

## Current Behavior
What currently happens?

## Expected Behavior
What should happen instead?

## Environment
- Browser: [e.g., Chrome 91.0]
- OS: [e.g., Windows 10]
- Device: [e.g., Desktop, Mobile]

## Additional Context
Add any other context, screenshots, or examples here.
```

### Examples

✅ **Good issue titles:**
```
Navigation menu not responsive on mobile devices
Add support for user profile customization
Login form validation errors not displaying
Performance issues when loading large datasets
```

❌ **Bad issue titles:**
```
#     # Empty title (not allowed)
X     # Too short (only 1 character)
```

## 🎯 Best Practices

### Commit Messages
- Use imperative mood ("Add feature" not "Added feature")
- Keep the subject line descriptive but concise
- Separate subject from body with a blank line
- Use the body to explain *what* and *why*, not *how*

### Pull Requests
- Link to relevant issues using keywords like "Fixes #123" or "Closes #456"
- Include screenshots for visual changes
- Keep PRs focused on a single feature or fix
- Ensure all CI checks pass before requesting review

### Issues
- Use clear, descriptive titles
- Provide enough context for others to understand the problem
- Include steps to reproduce for bugs
- Tag issues appropriately with labels

## 🔧 Automation

These guidelines are enforced automatically through repository settings and CI/CD pipelines. Non-compliant commits, PRs, or issues may be automatically flagged or rejected.

---

*Last updated: [Current Date]*
*For questions about these guidelines, please open an issue or contact the maintainers.*
