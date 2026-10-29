
<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/history.php";

// Send a JSON error response.
function sendError($message, $status = 400)
{
    http_response_code($status);

    echo json_encode([
        "error" => $message
    ]);

    exit;
}

// Read data from the GitHub API.
function githubGet($url)
{
    $options = [
        "http" => [
            "method" => "GET",
            "timeout" => 10,
            "ignore_errors" => true,
            "header" =>
                "User-Agent: GitHub-Profile-Finder\r\n" .
                "Accept: application/vnd.github+json\r\n" .
               
                ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        return [
            "status" => 0,
            "data" => null
        ];
    }

    $status = 0;

    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
            $status = (int) $matches[1];
        }
    }

    return [
        "status" => $status,
        "data" => json_decode($response, true)
    ];
}

// Accept the username from the search form.
$username = trim($_GET["username"] ?? "");

// GitHub usernames allow letters, numbers, and hyphens.
if (!preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$/', $username)) {
    sendError("Please enter a valid GitHub username.");
}

// Fetch the profile.
$profileResult = githubGet(
    "https://api.github.com/users/" . rawurlencode($username)
);

if ($profileResult["status"] === 404) {
    sendError("GitHub username not found.", 404);
}

if ($profileResult["status"] === 0) {
    sendError("Could not connect to GitHub. Check your internet connection.", 502);
}

if ($profileResult["status"] === 403 || $profileResult["status"] === 429) {
    sendError("GitHub API rate limit reached. Please try again later.", 429);
}

if ($profileResult["status"] !== 200 || !is_array($profileResult["data"])) {
    sendError("Unable to fetch the GitHub profile.", 502);
}

$profile = $profileResult["data"];

// Fetch public repositories owned by this user.
$repoResult = githubGet(
    "https://api.github.com/users/" .
    rawurlencode($username) .
    "/repos?per_page=100&type=owner&sort=updated"
);

if ($repoResult["status"] === 403 || $repoResult["status"] === 429) {
    sendError("GitHub API rate limit reached. Please try again later.", 429);
}

if ($repoResult["status"] !== 200 || !is_array($repoResult["data"])) {
    sendError("Unable to fetch repositories.", 502);
}

$repositories = $repoResult["data"];

// Sort from highest stars to lowest stars.
usort($repositories, function ($a, $b) {
    return ($b["stargazers_count"] ?? 0)
        <=> ($a["stargazers_count"] ?? 0);
});

// Keep only the six most popular repositories.
$repositories = array_slice($repositories, 0, 6);

// Save the search if MySQL is available.
saveSearch($pdo, $username);

// Return profile and repositories as JSON.
echo json_encode([
    "profile" => [
        "login" => $profile["login"],
        "name" => $profile["name"],
        "avatar_url" => $profile["avatar_url"],
        "bio" => $profile["bio"],
        "location" => $profile["location"],
        "followers" => $profile["followers"],
        "following" => $profile["following"],
        "public_repos" => $profile["public_repos"],
        "html_url" => $profile["html_url"]
    ],
    "repositories" => array_map(function ($repo) {
        return [
            "name" => $repo["name"],
            "description" => $repo["description"],
            "html_url" => $repo["html_url"],
            "stargazers_count" => $repo["stargazers_count"],
            "forks_count" => $repo["forks_count"],
            "language" => $repo["language"]
        ];
    }, $repositories)
]);
