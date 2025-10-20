@if($visits->isEmpty())
  <p class="text-muted mb-0">{{ $emptyMessage }}</p>
@else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Landing Page</th>
          <th class="text-right">Total</th>
          <th class="text-right">Unik</th>
          <th class="text-right">Primer</th>
          <th class="text-right">Sekunder</th>
        </tr>
      </thead>
      <tbody>
        @foreach($visits as $visit)
          <tr>
            <td>{{ $visit->page }}</td>
            <td class="text-right">{{ $visit->total_visits }}</td>
            <td class="text-right">{{ $visit->unique_visits }}</td>
            <td class="text-right">{{ $visit->primary_visits }}</td>
            <td class="text-right">{{ $visit->secondary_visits }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
