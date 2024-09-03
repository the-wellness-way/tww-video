import { getEl, clearErrors, errorDiv, successDiv } from "../helpers.js";
import { config } from "../config.js";
import { state } from "../state.js";
import { twwLoaderSVG } from "../loader.js";

export const initChangePasswordForm = () => {
    const form = getEl(config.twwChangePasswordForm);
    clearErrors('#error-message', true);
    
    if (!form) {
        return;
    }

    form.addEventListener('input', async (event) => {
        let currentPassword = form.querySelector('input[name="current_password"')
        let newPassword = form.querySelector('input[name="new_password"]');
        let confirmPassword = form.querySelector('input[name="confirm_password"]');
        let submitButton = form.querySelector('button[type="submit"]');

        if (event.target.name === 'current_password') {
            event.target.classList.remove('invalid');
            event.target.classList.add('valid');
        }

        let currentPwdValid = currentPassword.value ?? false;
        let newPwdValid = newPasswordIsValid(newPassword.value, currentPassword.value);
        let confirmPwdValid = confirmPasswordIsValid(confirmPassword.value, newPassword.value, currentPassword.value);

        if(false !== newPwdValid) {
            newPassword.classList.add('valid')
            newPassword.classList.remove('invalid')
            console.log(newPassword.value)
            console.log(newPwdValid)
        } else {
            newPassword.classList.add('invalid')
            newPassword.classList.remove('valid')
        }

        if(false !== confirmPwdValid) {
            confirmPassword.classList.add('valid')
            confirmPassword.classList.remove('invalid')
        } else {
            confirmPassword.classList.add('invalid')
            confirmPassword.classList.remove('valid')
        }

        if(currentPwdValid && newPwdValid && confirmPwdValid) {
            submitButton.disabled = false
        } else {
            submitButton.disabled = true
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors('#error-message', true);

        let closestParent = event.target.closest('#' + config.twwChangePasswordForm);
        let buttonLoader = closestParent.querySelector('.button-loader');

        if(buttonLoader) {
            buttonLoader.innerHTML = `<img src="${state.iconsPath}${twwLoaderSVG}.svg" alt="Loading...">`;
        }

        let button = closestParent.querySelector('.tww-subscribe-button-text');
        if(button) {
            button.style.visibility = 'hidden';
        }
    
        const formData = new FormData(form);
    
        changePasswordRequest({
            user_id: state.currentUserId,
            current_password: formData.get('current_password'),
            new_password: formData.get('new_password'),
            confirm_password: formData.get('confirm_password'),
        }).then(response => {
            if(button) {
                button.style.visibility = 'visible';
            }

            if(buttonLoader) {
                buttonLoader.innerHTML = '';
            }

            if (response.success) {
                form.appendChild(successDiv('Password updated successfully'));
            } else {
                form.appendChild(errorDiv(response.message));
            }
        }).catch(error => {
            form.appendChild(errorDiv('An error occurred. Please try again later.'));
        });
    });
}

export const newPasswordIsValid = (newPwd, currentPwd = null) => {
    if(!newPwd && currentPwd) {
        return false
    }

    if(!newPwd && !currentPwd) {
        return null;
    }

    if(newPwd == currentPwd) {
        return false;
    }

    return validatePassword(newPwd);
}

export const confirmPasswordIsValid = (confirmPwd = null, newPwd = null, currentPwd) => {
    if(!confirmPwd && !newPwd && currentPwd) {
        return false;
    }

    if(!confirmPwd && !newPwd) {
        return null;
    }

    if(confirmPwd != newPwd) {
        return false;
    }

    return validatePassword(confirmPwd);
}

//The password should contain at least 8 characters, one uppercase letter, one lowercase letter, and one number
export const validatePassword = (password) => {
    const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;

    return passwordRegex.test(password);
}

export const changePasswordRequest = async (data) => {
    const response = await fetch(state.endpoints.changePassword, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json',
        },
    });

    return await response.json();
}