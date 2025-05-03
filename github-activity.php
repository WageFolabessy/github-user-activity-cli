<?php

define('USER_AGENT', 'GithubActivityViewerCLI/1.0 (php-script; github.com/WageFolabessy/github-user-activity-cli)');
define('API_BASE_URL', 'https://api.github.com/users/');

function help(): void
{
    echo "Usage: php github-activity.php <github_username>\n";
    echo "Example: php github-activity.php WageFolabessy\n";
    exit(1);
}

function fetchFromApi(string $url): ?array
{
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: " . USER_AGENT . "\r\n" . "Accept: application/vnd.github.v3+json\r\n"
        ]
    ];

    $context = stream_context_create($options);

    $responseJson = @file_get_contents($url, false, $context);

    if ($responseJson === false) {
        if (isset($http_response_header) && is_array($http_response_header)) {
            if (strpos($http_response_header[0], '404 not found') !== false) {
                echo "Error: User not found or API URL is invalid.\n";
            } elseif (strpos($http_response_header[0], '403 Forbidden') !== false) {
                echo "Error: Failed to access the API. It is possible that the access limit(rate limit) is exceeded.\n";
                echo "Please try again later or use an auth token if You use it frequently.\n";
            } else {
                echo "Error: Failed to fetch from Github API ({$http_response_header[0]}).\n";
            }
        } else {
            echo "Error: Failed to call Github API. Please check your internet connection.\n";
        }

        return null;
    }

    $data = json_decode($responseJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error: Failed to process JSON respons from API. Response:\n" . substr($responseJson, 0, 200);
        return null;
    }

    if (!is_array($data)) {
        echo "Error: Accepted unexpect format from API.\n ";
        return null;
    }

    return $data;
}

function displayEventSummary(array $event): void
{
    $eventType = $event['type'] ?? "UnknownEvent";
    $repoName = $event['repo']['name'] ?? 'N/A';
    $actor = $event['actor']['login'] ?? 'N/A';
    $output = "- ";

    switch ($eventType) {
        case 'PushEvent':
            $commitCount = count($event['payload']['commits'] ?? []);
            $plural = $commitCount === 1 ? '' : 's';
            $ref = $event['payload']['ref'] ?? 'N/A';
            $branch = str_replace('refs/heads/', '', $ref);
            $output .= "Pushed {$commitCount} commit{$plural} to branch '{$branch}' in {$repoName}";
            break;

        case 'IssuesEvent':
            $action = $event['payload']['action'] ?? 'interacted with';
            $issueTitle = $event['payload']['issue']['title'] ?? '';
            $issueNumber = $event['payload']['issue']['number'] ?? '';

            if ($action === 'created') {
                $action = 'commented on';
            }

            $output .= ucfirst($action) . " issue #{$issueNumber} ({$issueTitle} in {$repoName})";
            break;

        case 'WatchEvent':
            $action = $event['payload']['action'] ?? 'watched';

            if ($action === 'started') {
                $output .= "Give a star on {$repoName}";
            } else {
                $output .= ucfirst($action) . " {$repoName}";
            }
            break;

        case 'CreateEvent':
            $refType = $event['payload']['ref_type'] ?? 'N/A';
            $refName = $event['payload']['ref'] ?? null;

            if ($refType === 'repository') {
                $output .= "Create a new '{$repoName}' repository";
            } elseif ($refType === 'branch' && $refName) {
                $output .= "Create a new '{$refName}' branch in {$repoName}";
            } elseif ($refType === 'tag' && $refName) {
                $output .= "Create a new '{$refName}' tag in {$repoName}";
            } else {
                $output .= "Create '{$refType}' in {$repoName}";
            }
            break;

        case 'DeleteEvent':
            $refType = $event['payload']['ref_type'] ?? 'N/A';
            $refName = $event['payload']['ref'] ?? 'N/A';
            $output .= "Delete {$refType} '{$refName}' in {$repoName}";
            break;

        case 'ForkEvent':
            $forkeeName = $event['payload']['forkee']['full_name'] ?? 'N/A';
            $output .= "Make a fork on {$repoName} into {$forkeeName}";
            break;

        case 'PullRequestEvent':
            $action = $event['payload']['action'] ?? 'interacted with';
            $prTitle = $event['payload']['pull_request']['title'] ?? '';
            $prNumber = $event['payload']['number'] ?? '?';
            $output .= ucfirst($action) . " pull request #{$prNumber} ('{$prTitle}') in {$repoName}";
            break;

        case 'PullRequestReviewCommentEvent':
        case 'PullRequestReviewEvent':
            $action = $event['payload']['action'] ?? 'intreacted with';
            $prNumber = $event['payload']['pull_request']['number'] ?? '?';
            if ($action === 'created' || $action === 'submitted') {
                $action = 'reviewed/commented on';
            }

            $output .= ucfirst($action) . " pull request #{$prNumber} in {$repoName}";
            break;

        case 'PublicEvent':
            $output .= "Change visibility of {$repoName} to public";
            break;

        default:
            return;
    }

    echo "{$output}" . "\n";
}

if ($argc !== 2) {
    echo "Error: Gituhub username is required.\n\n";
    help();
}

$username = $argv[1];

$apiUrl = API_BASE_URL . urlencode($username) . '/events';

echo "Get latest activity from user: {$username}...\n\n";

$events = fetchFromApi($apiUrl);

if ($events === null) {
    exit(1);
}

if (empty($events)) {
    echo "No recent public activity found from user: {$username}";
    exit(0);
}

echo "New Activities:\n";
echo "---------------\n";

foreach ($events as $event) {
    displayEventSummary($event);
}

exit(0);
