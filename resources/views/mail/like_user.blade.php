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
    <img style="height: 60px;" src="{{ $message->embed(storage_path()."/app/media/logo.svg")}}" alt="{{ config('app.name') }}"/>
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
<span
style="font-weight: 700;font-size: 24px;line-height: 28px;text-align: center;color: #4D4D4D;">
Hello{{ $user->name }}
</span>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td>
<table class="table-color" cellpadding="0" cellspacing="0" role="presentation" align="center" style="padding: 10px 0;background: #FDFDFD;width: 100%;text-align: center;">
<tr>
<td>
<span style="font-weight: 700;font-size: 18px;line-height: 28px;text-align: center;color: #4D4D4D;">
Someone liked your photo.
</span>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="width: 600px">
<table align="center" class="table-color" cellpadding="0" cellspacing="0" role="presentation"
style="width:100%;padding: 24px;font-weight: 400;font-size: 16px;line-height: 24px;color: #4D4D4D; background: #FDFDFD;">
<tr>
<td width="140" align="center">
<div class="holder">
<div class="user">
    <img src="{{ $message->embed($sender->avatar_url_thumbnail ?? storage_path()."/app/media/".$sender->gender.'.png')}}" style="object-fit: cover;object-position: top center; border-radius:50%;width: 120px;height: 120px;box-shadow: 0 0 0 5px lightgreen;border-radius: 50%;" />
</div>
</div>
</td>
<td style="border-top: 1px solid #edeff2;border-bottom: 1px solid #edeff2;padding-left: 15px;line-height: 2;" width="240">
    <div style="font-weight: 700;font-size: 24px;line-height: 28px;color: #7A5EEA;  display: flex;"><span>{{ $sender->name }}, {{ $sender->age }}</div>
    <div>{{ $sender->state }}, {{ $sender->country }}</div>
</td>
<td style="border-top: 1px solid #edeff2;border-bottom: 1px solid #edeff2">
    <a href="{{ route('mail.verify', ['token' => $user->token]) }}"
       style="padding: 12px 20px;font-weight: 500;font-size: 14px;letter-spacing: 2.2px;line-height: 16px;color: #7f5deb;border: 2px solid #7f5deb;border-radius: 20px;cursor:pointer;box-shadow: rgba(148, 45, 217, 0.35) 0px 0px 0px;text-decoration: none">
        WRITE
    </a>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td bgcolor="#191F2B" class="footer-color" style="border-bottom-right-radius: 16px; border-bottom-left-radius: 16px; background: #191F2B;">
<table align="center" bgcolor="#191F2B" cellpadding="0" cellspacing="0" role="presentation"
style="width:100%;background: #A077F3;padding: 20px 20px 20px 20px; border-bottom-right-radius: 16px; border-bottom-left-radius: 16px;font-weight: 500;font-size: 22px;line-height: 12px;color: #ffffff;">
<tr>
<td style="padding: 14px;">
</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</div>

