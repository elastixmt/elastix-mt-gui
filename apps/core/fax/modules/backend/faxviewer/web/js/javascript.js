$(document).ready(function(){ 
$('.checkall').click(function () {
     $(".neo-table-data-row").find(':checkbox').attr('checked', this.checked);
});
})
