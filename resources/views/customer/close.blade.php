<!DOCTYPE html>

<html>

<head>

<title>Saved</title>

</head>

<body>

<script>

if(window.opener){
    window.opener.document.getElementById('customer_search').value =
    "{{ $customer->customer_name }}";
    window.opener.document.getElementById('customer_id').value =
    "{{ $customer->id }}";
}

window.close();

</script>
</body>
</html>