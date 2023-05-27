

<center style="min-width:580px;width:100%">
      <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse;border-spacing:0;table-layout:fixed;min-width:100%;width:100%!important;color:#0a0836;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:14px;line-height:1.5;margin:0;padding:0" bgcolor="#f6fafb">
        <tbody><tr style="padding:0">
          <td align="center" valign="top" style="border-collapse:collapse!important;word-break:break-word;padding:10px 10px 0">
            <a rel="noopener noreferrer" style="color:#00b08c!important" >
               <h1>Ushescrow</h1>
</a>          </td>
         </tr>
        <tr style="padding:0">
          <td align="center" valign="top" style="border-collapse:collapse!important;word-break:break-word;min-width:100%;width:100%!important;margin:0;padding:20px 10px 30px">

            <table border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse:collapse;border-spacing:0;table-layout:auto;border-radius:10px;padding:0" bgcolor="#fff">
              
              <tbody><tr style="padding:0">
                <td align="left" valign="top" class="m_8503858993916122706content" style="border-collapse:collapse!important;word-break:break-word;padding:30px 40px">

                  

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border-spacing:0;table-layout:auto;padding:0">
  <tbody><tr style="padding:0">
    <td align="left" valign="middle" style="border-collapse:collapse!important;word-break:break-word;padding:0">

      
      <h1 style="word-break:normal;font-size:18px;font-weight:700;line-height:21px;padding-bottom:10px;margin:0">Welcome <a href="mailto:marvelwilsononit@gmail.com" style="color:#00b08c!important" target="_blank"><?php echo e($mailData['email']); ?></a>!</h1>

      <p style="font-size:14px;padding-bottom:10px;margin:0">Thank you for choosing Ushescrow.!</p>
      <p style="font-size:14px;padding-bottom:10px;margin:0"><?php echo e($mailData['msg']); ?></p>
    </td>
  </tr>
  <tr style="padding:0">
    <td align="center" valign="middle" style="border-collapse:collapse!important;word-break:break-word;padding:25px 0 35px">
      <table border="0" cellpadding="0" cellspacing="0" width="335" class="m_8503858993916122706button-block" style="border-collapse:separate;border-spacing:0;table-layout:auto;width:auto;padding:0">
        <tbody><tr style="padding:0">
        <?php if($mailData['code']): ?>
          <td align="center" valign="middle" class="m_8503858993916122706button" style="border-collapse:collapse!important;word-break:break-word;border-radius:25px;padding:10px 25px" bgcolor="#00b08c">
            <a style="color:#fff!important;display:block;font-size:14px;font-weight:700;text-decoration:none"><?php echo e($mailData['code']); ?></a>
          </td>
          <?php endif; ?>
        </tr>
      </tbody></table>
    </td>
  </tr>
<tr style="padding:0">
<?php if($mailData['code']): ?>
    <td align="left" valign="middle" style="border-collapse:collapse!important;word-break:break-word;padding:0">
      <p style="font-size:14px;padding-bottom:10px;margin:0">Please Make sure to verify your account.</p>
      <br>
      <p style="font-size:14px;padding-bottom:10px;margin:0">If you didn't request this, please ignore this email.</p>

    </td>
  <?php endif; ?>
  </tr>
</tbody></table>


                </td>
              </tr>

              
              <tr style="padding:0">
                <td align="left" valign="middle" class="m_8503858993916122706inner-footer" style="border-collapse:collapse!important;word-break:break-word;padding:0 40px 30px">

                  
                  <table border="0" cellpadding="0" cellspacing="0" width="50%" style="border-collapse:collapse;border-spacing:0;table-layout:auto;padding:0">
                    <tbody><tr style="padding:0">
                      <td align="left" valign="middle" style="border-collapse:collapse!important;word-break:break-word;border-top-width:1px;border-top-color:#e4e4e9;border-top-style:solid;font-size:12px;line-height:1.5;padding:20px 0 0">
                          <strong>Yours, Ushescrow Inc</strong><br>
                          <a href="mailto:support@ushescrow.com" rel="noopener noreferrer" style="color:#00b08c!important" target="_blank">support@ushescrow.com</a>
                      </td>
                    </tr>
                  </tbody></table>

                </td>
              </tr>

            </tbody></table>

          </td>
        </tr>
        <tr><td><br>
        </td></tr>
      </tbody></table>
    </center>

<?php /**PATH C:\Users\USER\Desktop\UshEscrow\resources\views/email.blade.php ENDPATH**/ ?>