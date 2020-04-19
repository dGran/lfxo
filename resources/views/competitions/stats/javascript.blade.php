<script>
	$(function() {
		$('.item').on('click', function() {
			var id = $(this).attr("data-id");
			var element = $('#'+id);
			if (element.hasClass('d-none')) {
				$(element).removeClass('d-none');
				$(element).removeClass('bounceOut');
				$(element).addClass('fadeIn');
			} else {
				$(element).removeClass('fadeIn');
				$(element).addClass('bounceOut');
				setTimeout(function() {
					$(element).addClass('d-none');
				}, 650);
			}
		});
	});
</script>