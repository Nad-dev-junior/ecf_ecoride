document.addEventListener('DOMContentLoaded', 
function(e){
    const form = document.getElementByid('confirm-form');
    const confirmBox = document.getElementById('confirm-box');

    // Validation formulaire (Case a cocher)
    form.addEventListener('submit', function(e){
        // si jamais confirmBox n'est tcheker le formulaire ne sera pas soumis
        if(!confirmBox.checked){
            e.prevenDefault();
            confirmBox.classList.add('is-invalid');

            // Creation du Message d' erreur
            if(!document.querySelector('#confirm-form ...confirm-alert')){
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert confirm-alert alert-danger mt-3';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Vous devez confirmer votre participation.`;

                const checkboxContainer = document.querySelector('.checkbox-container');
                if(checkboxContainer){
                    checkboxContainer.before(errorDiv);
                }
            }
        }
    });

    // Masquer les erreurs lorsqu'on coche une case 
    confirmBox.addEventListener('change' , function(){
        if(this.cheked){
            this.classList.remove('is-invalid');
            const errorDiv = document.querySelector('.confirm-alert')
            if(errorDiv){
                errorDiv.remove();
            }
        }
    });
})