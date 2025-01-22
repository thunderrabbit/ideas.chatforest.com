document.getElementById("ideaForm").addEventListener("submit", async (event) => {
    event.preventDefault();

    const niche = document.getElementById("niche").value;
    const resultsSection = document.getElementById("results");
    const ideaList = document.getElementById("ideaList");

    // Clear previous results
    ideaList.innerHTML = "";
    resultsSection.classList.add("hidden");

    // Show loading state
    const loadingMessage = document.createElement("li");
    loadingMessage.textContent = "Generating ideas...";
    ideaList.appendChild(loadingMessage);

    try {
        // Send the POST request to the API
        const response = await fetch("/generate-ideas", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ niche }),
        });

        const data = await response.json();

        // Clear loading message
        ideaList.innerHTML = "";

        if (response.ok) {
            // Display generated ideas
            data.ideas.split("\n").forEach((idea) => {
                const listItem = document.createElement("li");
                listItem.textContent = idea;
                ideaList.appendChild(listItem);
            });
            resultsSection.classList.remove("hidden");
        } else {
            // Show error message
            const errorItem = document.createElement("li");
            errorItem.textContent = `Error: ${data.error}`;
            ideaList.appendChild(errorItem);
        }
    } catch (error) {
        // Handle network errors
        ideaList.innerHTML = "";
        const errorItem = document.createElement("li");
        errorItem.textContent = `Error: ${error.message}`;
        ideaList.appendChild(errorItem);
    }
});
