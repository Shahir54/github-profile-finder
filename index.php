
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GitHub Profile Finder</title>

    <link rel="stylesheet" href="stytle.css">
</head>
<body>

    <header class="header">
        <h1>GitHub Profile Finder</h1>
        <p>Discover GitHub profiles and popular repositories</p>
    </header>

    <main class="container">

        <section class="search-card">
            <h2>Find a GitHub Profile</h2>

            <form id="searchForm">
                <label for="username">GitHub Username</label>

                <div class="search-box">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Example: torvalds"
                        maxlength="39"
                        required
                    >

                    <button type="submit" id="searchButton">
                        Search
                    </button>
                </div>
            </form>

            <p id="message" role="status" aria-live="polite"></p>
        </section>

        <section id="profileSection" class="profile-card" hidden>
            <div id="profileDetails"></div>
        </section>

        <section id="repositorySection" hidden>
            <h2>Popular Repositories</h2>
            <div id="repositories" class="repository-grid"></div>
        </section> <br><br>

        <section class="history-card">
            <h2>Recent Searches</h2>
            <ul id="historyList">
                <li>Loading search history...</li>
            </ul>
        </section>

    </main>

    <footer>
        <p>GitHub Profile Finder | College Mini Project</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
