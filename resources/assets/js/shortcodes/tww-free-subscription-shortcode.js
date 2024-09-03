import { getEl, getAll, clearErrors, clearSuccess, errorDiv, successDiv } from "../helpers.js";
import { config } from "../config.js";
import { state } from "../state.js";
import { twwLoaderSVG } from "../loader.js";
import { createPasswordModal, createLoginFields } from "../components/modal-login.js";

export const initForm = () => {  
    if(getEl(config.twwRegistrationFree)) {
        clearErrors();
        clearErrors('.error-message', true);
        clearSuccess();

        const subscribeForms = getAll('tww-plus-subscribe-form');
        subscribeForms.forEach((form) => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearErrors();
                clearErrors('.error-message', true);
                clearSuccess();
                
                let closestParent = e.target.closest('.tww-plus-subscribe-form');
                let email = closestParent.querySelector('#' + config.twwPlusEmail).value ?? null;

                if(email) {
                    let buttonLoader = closestParent.querySelector('#tww-plus-button-loader');
                    if(buttonLoader) {
                        buttonLoader.innerHTML = `<img src="${state.iconsPath}/${twwLoaderSVG}.svg" alt="Loading...">`;
                    }

                    let button = closestParent.querySelector('#' + config.twwSubscribeButtonText);
                    if(button) {
                        button.style.visibility = 'hidden';
                    }

                    createMember({ email: email, username: email }).then((response) => {
                        if(button) {
                            button.style.visibility = 'visible';
                        }

                        if(buttonLoader) {
                            buttonLoader.innerHTML = '';
                        }

                        if(response.message && 'success' === response.status && response.data && response.data.id) {
                            closestParent.appendChild(successDivAlt(response.message + ' Reloading page.'));

                            if(response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else if (response.data && 400 === response.data.status) {
                            if(response.code && 'member_exists' === response.code) {
                                const fields = createLoginFields(email);
                                const message = successDivAlt('You are already registered. Please login or reset your password.');
                                createPasswordModal(fields, message);
                            } else {
                                //refactor getEl(config.twwRegistrationFree).appendChild(errorDiv(response.message)); to closest form parent
                                // so e.target.id can be used to target the form
                                let closestParent = e.target.closest('.tww-plus-subscribe-form');
                                closestParent.appendChild(errorDiv(response.message));
                            }
                        }
                    }).catch((error) => {             
                        getEl(config.twwRegistrationFree).appendChild(errorDiv(error.message));
                    });
                } else {        
                    let closestParent = e.target.closest('.tww-plus-subscribe-form');
                    closestParent.appendChild(errorDiv('Please enter a valid email address.'));
                }           
            });
        });
    }
};

export const createMember = async (data) => {
    const response = await fetch(state.endpoints.createMember, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': state.restNonce,
        },
        body: JSON.stringify(data),
    });

    return await response.json();
}

export const successDivAlt = (message) => {   
    const div = document.createElement('div');
    div.id = 'success-message';
    div.classList.add('tww-plus-success');
    div.style.color = 'green';

    const p = document.createDocumentFragment('p');
    p.textContent = message;

    div.appendChild(p);

    div.innerHTML = message;

    return div;
}

