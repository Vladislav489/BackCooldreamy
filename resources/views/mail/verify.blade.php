<span style=" display: none !important; visibility: hidden;mso-hide: all !important;font-size: 1px;color: #D9D9D9;line-height: 1px;max-height: 0px;max-width: 0px;opacity: 0;overflow: hidden;">
&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
</span>
<style>
@media (prefers-color-scheme: dark) {.table-color {background: #FDFDFD;} .background-color {background: #D9D9D9;} .footer-color {background: #191F2B;}}
</style>
<div class="background-color" style="min-width: 600px; background: #D9D9D9;padding-top: 40px;padding-bottom: 40px;padding-left: 20px;padding-right: 20px;">
    <div style="">
        <table cellpadding="0" align="center" cellspacing="0" role="presentation" style="width: 600px;">
            <tr>
                <td class="table-color" align="center" style="border-bottom: 1px solid #edeff2;border-top-left-radius: 16px;border-top-right-radius: 16px;background: #FDFDFD;">
                            <table cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="padding: 24px 0;height: 60px;">
                                        <img style="height: 60px;" src="{{ $message->embed(storage_path()."/app/media/logo.png")}}" alt="{{ config('app.name') }}"/>
                                    </td>
                                </tr>
                            </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table class="table-color" cellpadding="0" cellspacing="0" role="presentation" align="center" style="padding: 24px 0;background: #FDFDFD;width: 100%;text-align: center;">
                        <tr>
                            <td>
                                <span style="font-weight: 700;font-size: 24px;line-height: 28px;text-align: center;color: #4D4D4D;"> Email Confirmation</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 600px">
                    <table class="table-color" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;padding: 24px;font-weight: 400;font-size: 16px;line-height: 24px;color: #4D4D4D; background: #FDFDFD;">
                        <tr>
                            <td style="font-weight: 700;font-size: 18px;line-height: 28px;text-align: center;color: #6a61ec;">
                                Hello, <b>{{ $name }}</b>!
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500;font-size: 14px;line-height: 28px;text-align: center;color: #0a0c0d;" >
                                Click the button below to confirm your email.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table class="table-color" cellpadding="0" cellspacing="0" role="presentation" style="padding: 16px 0; background: #FDFDFD;width: 600px;text-align: center">
                        <tr>
                            <td style="padding: 12px 0">
                                <a href="{{ route('mail.verify', ['token' => $token]) }}" style="background: linear-gradient(166deg,#a058ea,#6a61ec);padding: 12px 20px;font-weight: 500;font-size: 14px;line-height: 16px;color: #FFFFFF;border: 1px solid rgba(143,20,126,0.85);border-radius: 20px;cursor:pointer;box-shadow: rgba(148, 45, 217, 0.35) 0px 0px 0px;text-decoration: none"> Confirm Email</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 20px;">
                                <strong>If you found this email in your SPAM folder - be sure to move the email to your inbox to receive the right emails from our team.</strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td bgcolor="#191F2B" class="footer-color" style="border-bottom-right-radius: 16px; border-bottom-left-radius: 16px; background: #191F2B;">
                    <table align="center" bgcolor="#191F2B" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;background: #A077F3;padding: 20px 20px 20px 20px; border-bottom-right-radius: 16px; border-bottom-left-radius: 16px;font-weight: 500;font-size: 22px;line-height: 12px;color: #ffffff;">
                        <tr>
                            <td style="padding: 14px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

