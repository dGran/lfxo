<div class="modal-content">
    <div class="modal-header bg-light">
    	<h4 class="m-0">Historial de economía</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

	<div class="d-table">
		<div class="d-table-cell pl-2 pr-2 pt-2">
    		<img src="{{ $participant->logo() }}" alt="" width="60">
		</div>
    	<div class="d-table-cell w-100 pt-2 pb-2 pr-2 align-middle">
	    	<span>
	    		{{ $participant->name() == 'undefined' ? '' : $participant->name() }}
	    	</span>
		    <small class="text-black-50 d-block">
		        @if ($participant->sub_name() == 'undefined')
		            <span class="badge badge-danger p-1 mt-1">SIN USUARIO</span>
		        @else
		            <strong>Manager: </strong>{{ $participant->sub_name() }}
		        @endif
		    </small>
	    </div>
	</div>

    <div class="modal-body">
    	<table>
	        <colgroup>
	            <col width="0%" />
	            <col width="0%" />
	            <col width="100%"/>
	            <col width="0%" />
	        </colgroup>
	        <thead>
	            <tr>
					<th scope="col" class="p-1">Fecha</th>
					<th scope="col" class="text-center pl-3 pr-3">E/S</th>
					<th scope="col">Descripción</th>
					<th scope="col" class="text-right">Cantidad</th>
				</tr>
	        </thead>

	        <tbody>
	    	@foreach ($participant->cash_history as $cash_history)
	    		<tr class="border-top">
	    			<td class="p-1">
                        <small class="text-nowrap">{{ \Carbon\Carbon::parse($cash_history->created_at)->format('d/m/Y')}}</small>
	    			</td>
					<td class="text-center">
						@if ($cash_history->movement == "E")
							<i class="fas fa-piggy-bank text-success"></i>
						@else
							<i class="fas fa-piggy-bank text-danger"></i>
						@endif
					</td>
					<td>{{ $cash_history->description }}</td>
					<td class="text-right">
						@if ($cash_history->movement == "S")
						-
						@endif
						{{ $cash_history->amount }} M
					</td>
	    		</tr>
	    	@endforeach
	    	</tbody>
    	</table>
    </div>
    <div class="modal-footer bg-light">
		<h5 class="text-right">
			Presupuesto: {{ $participant->budget_formatted() }}
		</h5>
    </div>
</div>