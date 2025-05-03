# GitHub User Activity Viewer (CLI)

A simple command-line interface (CLI) tool built with PHP to fetch and display the recent public activity of a specified GitHub user directly in your terminal.

This project is part of the roadmap.sh projects collection. You can find the project description here:
[https://roadmap.sh/projects/github-user-activity](https://roadmap.sh/projects/github-user-activity)

## Features

- Fetches recent public events for a GitHub user via the GitHub API.
- Displays a human-readable summary of common events (Pushes, Issues, Stars/Watches, Forks, Pull Requests, Comments, Creations, Deletions, etc.).
- Handles basic errors like invalid usernames or API request failures.
- Runs directly from the command line.
- Built with plain PHP, no external HTTP client libraries required.

## Requirements

- **PHP**: Version 7.4 or higher is recommended (due to usage of arrow functions and nullable types). Ensure PHP is installed and accessible from your command line (`php -v`).
- **Internet Connection**: Required to access the GitHub API.
- **PHP Configuration**: `allow_url_fopen` must be enabled in your `php.ini` configuration file. This is typically enabled by default, but necessary for `file_get_contents()` to fetch data from URLs.

## Setup

1.  Download the `github-activity.php` script file from this project.
2.  Save it to a directory on your local machine.

## How to Run

1.  Open your terminal or command prompt.
2.  Navigate (`cd`) to the directory where you saved the `github-activity.php` file.
3.  Execute the script using the `php` command, followed by the script name and the target GitHub username:

    ```bash
    php github-activity.php <github_username>
    ```

4.  Replace `<github_username>` with the actual GitHub username whose activity you want to view.

**Examples:**

```bash
# View activity for the user 'octocat'
php github-activity.php octocat

# View activity for the user 'torvalds'
php github-activity.php torvalds

# View activity for your own username
php github-activity.php your-github-username
```
