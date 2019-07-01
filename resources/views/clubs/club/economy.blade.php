<div class="club-info">
	<h4 class="title-position">
		<div class="container clearfix">
			<span>Economía</span>
		</div>
	</h4>
	<div class="container p-3">
		<ul class="details">
			<li>
				Presupuesto: <strong>{{ $participant->budget() }} M.</strong>
			</li>
			<li>
				Salarios: <strong>{{ $participant->salaries() }} M.</strong>
			</li>
			<li>
				Salario medio: <strong>{{ number_format($participant->salaries_avg(), 2) }} M.</strong>
			</li>
		</ul>
	</div>
</div>