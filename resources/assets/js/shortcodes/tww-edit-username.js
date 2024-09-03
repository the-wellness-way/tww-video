import { getEl, clearErrors, errorDiv, successDiv } from "../helpers.js";
import { config } from "../config.js";
import { state } from "../state.js";
import { twwLoaderSVG, loaderDefault } from "../loader.js";

export const initEditUserName = () => {
    const editUserNameForm = getEl(config.twwEditUserNameForm);
    const submitButton = getEl(config.twwEditUserButton);
    const emailInput = getEl(config.twwUserForm.email);
    
    if(emailInput) {
        //validate email when user leaves the email input field
        emailInput.addEventListener('blur', (e) => {
            if(!validateEmailNow(e.target.value)) {
                e.target.classList.add('invalid');
                e.target.classList.remove('valid');
            } else {
                e.target.classList.remove('invalid');
                e.target.classList.add('valid');
            }
        });
    }
    if(submitButton) {
        submitButton.addEventListener('click', async (e) => {
            e.preventDefault();

            e.target.innerHTML = '';
            e.target.appendChild(loaderDefault());

            if(!validateEmailNow(getEl(config.twwUserForm.email).value)) {
                alert('Please enter a valid email address');
                e.target.innerHTML = 'Save';
                return;
            }

            let data = {
                user_id: state.currentUserId,
                first_name: getEl(config.twwUserForm.firstName).value,
                last_name: getEl(config.twwUserForm.lastName).value,
                email: getEl(config.twwUserForm.email).value,
            }

            updateUser(data).then(response => {
                if(response.success) {
                    alert('Account information updated successfully');
                } else {
                    if(response.message) {
                        alert(response.message);
                    } else {
                        alert('Failed to update user info. Please make sure you have entered valid data.');
                    }
                }

                e.target.innerHTML = 'Save';
            }).catch(error => {
                if(error.message) {
                    alert(error.message);
                } else {
                    alert('Failed to update user info. Please make sure you have entered valid data.');
                }
                e.target.innerHTML = 'Save';
            });

            
        });
    }
}

export const updateUser = async (data) => {
    const response = await fetch(state.endpoints.updateUser, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.twwForms.restNonce,
        },
    });

    return await response.json();
}

export const validateEmailNow = (email) => {
    const re = /\S+@\S+\.\S+/;
    return re.test(email);
}