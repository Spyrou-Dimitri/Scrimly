import { settings } from './settings.js';
export const Password = {
    toggleButton: document.querySelector(settings.passwordToggleButton),
    passwordInput: document.querySelector(settings.passwordInput),
    passwordRules: {
        length: document.querySelector(settings.passwordRules.length),
        uppercase: document.querySelector(settings.passwordRules.uppercase),
        number: document.querySelector(settings.passwordRules.number),
    },
    passwordRulesIconsError: {
        length: document.querySelector(settings.passwordRulesIconsError.length),
        uppercase: document.querySelector(settings.passwordRulesIconsError.uppercase),
        number: document.querySelector(settings.passwordRulesIconsError.number),
    },
    passwordRulesIconsSuccess: {
        length: document.querySelector(settings.passwordRulesIconsSuccess.length),
        uppercase: document.querySelector(settings.passwordRulesIconsSuccess.uppercase),
        number: document.querySelector(settings.passwordRulesIconsSuccess.number),
    },
    init() {
        this.isToggle();
        console.log(this.passwordInput.value);
        this.securePassword();
    },
    isToggle() {
        this.toggleButton.addEventListener('click', () => {
            this.passwordInput.type = this.passwordInput.type === 'password' ? 'text' : 'password';
        });
    },
    setRuleState(ruleKey, isValid) {
        const rule = this.passwordRules[ruleKey];
        const iconError = this.passwordRulesIconsError[ruleKey];
        const iconSuccess = this.passwordRulesIconsSuccess[ruleKey];

        rule.classList.toggle('text-input-error', ! isValid);
        rule.classList.toggle('text-green-500', isValid);
        iconError.classList.toggle('hidden', isValid);
        iconSuccess.classList.toggle('hidden', ! isValid);
    },
    securePassword() {
        this.passwordInput.addEventListener('input', () => {
            const value = this.passwordInput.value;

            this.setRuleState('length', value.length >= 8);
            this.setRuleState('uppercase', this.hasUpperCase(value));
            this.setRuleState('number', this.hasNumber(value));
        });
    },
    hasUpperCase(string) {
        return /[A-Z]/.test(string);
    },
    hasNumber(string) {
        return /\d/.test(string);
    },
}
