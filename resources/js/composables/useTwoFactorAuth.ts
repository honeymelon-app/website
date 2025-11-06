import { computed, ref } from 'vue';

/**
 * Two-factor authentication composable
 *
 * Note: Two-factor authentication is now managed by Cerberus IAM.
 * This composable is kept for compatibility but returns stub data.
 * Users should configure 2FA through the Cerberus IAM dashboard.
 */

const errors = ref<string[]>([]);
const manualSetupKey = ref<string | null>(null);
const qrCodeSvg = ref<string | null>(null);
const recoveryCodesList = ref<string[]>([]);

const hasSetupData = computed<boolean>(
    () => qrCodeSvg.value !== null && manualSetupKey.value !== null,
);

export const useTwoFactorAuth = () => {
    const fetchQrCode = async (): Promise<void> => {
        // 2FA is managed by Cerberus IAM
        errors.value.push('Two-factor authentication is managed through Cerberus IAM');
        qrCodeSvg.value = null;
    };

    const fetchSetupKey = async (): Promise<void> => {
        // 2FA is managed by Cerberus IAM
        errors.value.push('Two-factor authentication is managed through Cerberus IAM');
        manualSetupKey.value = null;
    };

    const clearSetupData = (): void => {
        manualSetupKey.value = null;
        qrCodeSvg.value = null;
        clearErrors();
    };

    const clearErrors = (): void => {
        errors.value = [];
    };

    const clearTwoFactorAuthData = (): void => {
        clearSetupData();
        clearErrors();
        recoveryCodesList.value = [];
    };

    const fetchRecoveryCodes = async (): Promise<void> => {
        // 2FA is managed by Cerberus IAM
        errors.value.push('Two-factor authentication is managed through Cerberus IAM');
        recoveryCodesList.value = [];
    };

    const fetchSetupData = async (): Promise<void> => {
        // 2FA is managed by Cerberus IAM
        clearErrors();
        qrCodeSvg.value = null;
        manualSetupKey.value = null;
    };

    return {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        errors,
        hasSetupData,
        clearSetupData,
        clearErrors,
        clearTwoFactorAuthData,
        fetchQrCode,
        fetchSetupKey,
        fetchSetupData,
        fetchRecoveryCodes,
    };
};
