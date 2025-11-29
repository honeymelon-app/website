<x-mail::message>
# Thank You for Purchasing Honeymelon!

Your license key is ready. Please keep this email safe as it contains your license key.

## Your License Key

<x-mail::panel>
<code style="font-family: monospace; font-size: 14px; word-break: break-all;">{{ $licenseKey }}</code>
</x-mail::panel>

## How to Activate

1. Download Honeymelon from the link below
2. Open the application
3. When prompted, paste your license key
4. You're ready to start converting!

<x-mail::button :url="$downloadUrl">
Download Honeymelon
</x-mail::button>

## License Details

- **Valid for:** Honeymelon v{{ $maxMajorVersion }}.x
- **Devices:** Unlimited personal devices
- **Updates:** All v{{ $maxMajorVersion }}.x updates included

## Need Help?

If you have any questions or issues activating your license, please contact us at support@honeymelon.app.

Thanks,<br>
The Honeymelon Team
</x-mail::message>
