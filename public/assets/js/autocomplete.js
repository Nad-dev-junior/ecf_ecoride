document.addEventListener('DOMContentLoaded', () => {
    const departInput = document.getElementById('lieu_depart');
    const arriveeInput = document.getElementById('lieu_arrivee');
    const departResults = document.getElementById('depart-results');
    const arriveeresults = document.getElementById('arrivee-results');
    const autocompleteUrl = document.getElementById('autocomplete-url');

    function set_up_autocompletion(input, resultsContainer) {
        let timeoutId;

        input.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            if (query.length < 3) {
                resultsContainer.style.display = 'none'
                return;
            }

            console.log(`${autocompleteUrl.value}?query=${encodeURIComponent(query)}`);
            timeoutId = setTimeout(function () {
                fetch(`${autocompleteUrl.value}?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(addresses => {
                        console.log(addresses);
                        displayResults(addresses, resultsContainer, input);
                    })
                    .catch((error) => {
                        console.log('Erreur autocompletion: ', error);
                    });
            }, 300);
        })

        // Cacher les resultats quand on clique ailleurs
        document.addEventListener('click', function (e) {
            // console.log(input.contains(e.target));
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        })

    }

    set_up_autocompletion(departInput, departResults);
    set_up_autocompletion(arriveeInput, arriveeresults);

    // Fonction de formatage et d'affichage des resultats suggeres
    function displayResults(results, container, input) {
        container.innerHTML = '';

        if (results.length === 0) {
            container.style.display = 'none';
            return;
        }

        results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.textContent = result.label;
            item.addEventListener('click', () => {
                input.value = result.label;
                container.style.display = 'none';
            })

            container.appendChild(item);
        })

        container.style.display = 'block';
    }

    // Preremplir le champ date avec la date du jour en cours (L'utilisateur pourra toujours la changer)
    const dateInput = document.getElementById('date-depart');
    if (!dateInput.value || dateInput.value.trim() === '') {
        const now = new Date();
        // console.log(now.toISOString());
        dateInput.value = now.toISOString().split('T')[0];
    }

     /*===== Reservation covoiturage prochiane date suggeree =======*/
     const nextCarpool = document.getElementById('next-carpool');
     const nextDateField = document.getElementById('next-date');
     if (nextCarpool) {
         nextCarpool.addEventListener('click', () => {
             dateInput.value = nextDateField.value;
             document.getElementById('search-form').submit();
         })
     }
});