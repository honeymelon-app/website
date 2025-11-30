<x-mail::message>
# Welcome to Honeymelon!

Thank you for your purchase! Your license key is ready below. Please keep this email safe.

## Your License Key

<x-mail::panel>
{{ $licenseKey }}
</x-mail::panel>

## Getting Started

1. **Download** Honeymelon using the button below
2. **Open** the application on your Mac
3. **Paste** your license key when prompted
4. **Convert** your media with blazing fast speed!

<x-mail::button :url="$downloadUrl">
Download Honeymelon
</x-mail::button>

## Your License Details

- **Version:** Honeymelon v{{ $maxMajorVersion }}.x (all updates included)
- **Devices:** Unlimited Macs you own
- **Support:** Email support included

## Need Help?

Check out our [documentation](https://docs.honeymelon.app) or contact us at [support@honeymelon.app](mailto:support@honeymelon.app).

Happy converting!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
