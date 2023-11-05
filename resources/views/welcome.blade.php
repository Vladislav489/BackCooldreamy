API SERVER COOL DATE! <br>
donjenflo task
@php phpinfo(); @endphp



<script>
   function() {
       var request = new XMLHttpRequest();
       // Instantiating the request object
       request.open("POST", "https://api.cooldreamy.com/add/token/firebase",false);
       // Defining event listener for readystatechange event
       request.onreadystatechange = function () {
           // Check if the request is compete and was successful
           if (this.readyState === 4 && this.status === 200) {
               // Inserting the response from server into an HTML element
           }
       };
       // Retrieving the form data
       var formData = new FormData();
       formData.append("toket_fireBase","Сюда  вставку токена " )
       formData.append("ip","Сюда  вставку IP " )
       // Sending the request to the server
       request.send(formData);
       return request.status; // 200
   }
</script>
