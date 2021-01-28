<!DOCTYPE html>
<html>
<head>
  <title>DOKU Payment Gateway</title>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" rel="stylesheet">

  <?php if ($payment_env == 'production') {?>
    <script src="https://staging.doku.com/doku-js/assets/js/doku-1.2.js"></script>
    <link href="https://staging.doku.com/doku-js/assets/css/doku.css" rel="stylesheet">
  <?php } else {?>
    <script src="https://staging.doku.com/doku-js/assets/js/doku-1.2.js"></script>
    <link href="https://staging.doku.com/doku-js/assets/css/doku.css" rel="stylesheet">
  <?php }?>
</head>
<body>

<form action="" method="POST" id="payment-form">
  <div doku-div='form-payment'>
    <input id="doku_token" name="doku_token" type="hidden" />
    <input id="doku_pairing_code" name="doku_pairing_code" type="hidden" />
    <!-- <input id="doku_mall_id" name="merchant_code" value="8286" type="hidden" /> -->
  </div>
</form>

<script type="text/javascript">
$(function() {
 var data = new Object();
 data.req_merchant_code = '{{ $mallId }}';
 data.req_chain_merchant = 'NA';
 data.req_payment_channel = '15'; // ‘15’ = credit card
 data.req_transaction_id = '{{ $invoice }}';
 data.req_currency = '360';
 data.req_amount = '{{ $amount }}';
 data.req_words = '{{ $words }}';
 data.req_form_type = 'full';
 data.req_server_url = 'http://localhost:8000/getPayment' //ini link ke api
getForm(data, '<?php echo $payment_env; ?>');
});
</script>

</body>
</html>
