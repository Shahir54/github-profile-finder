
const searchForm = document.getElementById("searchForm");
const usernameInput = document.getElementById("username");
const searchButton = document.getElementById("searchButton");
const message = document.getElementById("message");

const profileSection = document.getElementById("profileSection");
const profileDetails = document.getElementById("profileDetails");

const repositorySection = document.getElementById("repositorySection");
const repositoriesContainer = document.getElementById("repositories");

const historyList = document.getElementById("historyList");

// Escape text before inserting it into HTML.
function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (char) {
        const replacements = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        };

        return replacements[char];
    });
}

// Search for a GitHub username.
searchForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const username = usernameInput.value.trim();

    if (!username) {
        message.textContent = "Please enter a GitHub username.";
        return;
    }

    searchButton.disabled = true;
    searchButton.textContent = "Searching...";
    message.textContent = "Loading GitHub profile...";

    profileSection.hidden = true;
    repositorySection.hidden = true;

    try {
        const response = await fetch(
            "api.php?username=" + encodeURIComponent(username)
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || "Something went wrong.");
        }

        displayProfile(data.profile);
        displayRepositories(data.repositories);

        profileSection.hidden = false;
        repositorySection.hidden = false;

        message.textContent = "Profile loaded successfully.";

        loadHistory();

    } catch (error) {
        message.textContent = error.message;
    } finally {
        searchButton.disabled = false;
        searchButton.textContent = "Search";
    }
});

// Display the GitHub profile.
function displayProfile(profile) {
    const name = profile.name || profile.login;
    const bio = profile.bio || "No bio available.";
    const location = profile.location || "Not provided";

    profileDetails.innerHTML = `
        <div class="profile-info">
            <img
                class="avatar"
                src="${escapeHTML(profile.avatar_url)}"
                alt="GitHub profile picture"
            >

            <div>
                <h2>${escapeHTML(name)}</h2>

                <p>@${escapeHTML(profile.login)}</p>

                <p class="bio">${escapeHTML(bio)}</p>

                <p>Location: ${escapeHTML(location)}</p>

                <a
                    class="profile-link"
                    href="${escapeHTML(profile.html_url)}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    View GitHub Profile
                </a>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <strong>${profile.public_repos}</strong><br>
                Public Repositories
            </div>

            <div class="stat">
                <strong>${profile.followers}</strong><br>
                Followers
            </div>

            <div class="stat">
                <strong>${profile.following}</strong><br>
                Following
            </div>
        </div>
    `;
}

// Display the six most popular repositories.
function displayRepositories(repositories) {
    repositoriesContainer.innerHTML = "";

    if (repositories.length === 0) {
        repositoriesContainer.textContent =
            "No public repositories found.";
        return;
    }

    repositories.forEach(function (repo) {
        const card = document.createElement("article");
        card.className = "repo-card";

        card.innerHTML = `
            <h3>
                <a
                    class="repo-link"
                    href="${escapeHTML(repo.html_url)}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    ${escapeHTML(repo.name)}
                </a>
            </h3>

            <p class="repo-description">
                ${escapeHTML(repo.description || "No description available.")}
            </p>

            <p class="repo-stats">
                ⭐ ${repo.stargazers_count}
                &nbsp; | &nbsp;
                Forks: ${repo.forks_count}
            </p>

            <p>Language: ${escapeHTML(repo.language || "Not specified")}</p>
        `;

        repositoriesContainer.appendChild(card);
    });
}

// Load recent searches from MySQL.
async function loadHistory() {
    try {
        const response = await fetch("includes/history.php");
        const history = await response.json();

        if (!response.ok) {
            throw new Error("History unavailable.");
        }

        historyList.innerHTML = "";

        if (history.length === 0) {
            historyList.textContent = "No searches yet.";
            return;
        }

        history.forEach(function (item) {
            const listItem = document.createElement("li");

            listItem.textContent =
                item.username + " — " + item.searched_at;

            historyList.appendChild(listItem);
        });

    } catch (error) {
        historyList.textContent =
            "Search history is currently unavailable.";
    }
}

// Load history when the webpage opens.
loadHistory();
