<tr class="border-t" id="weather-row-{{ $weather_log->id }}">
    <td class="px-4 py-2">Jam {{ \Carbon\Carbon::parse($weather_log->time)->format('H:i') }}</td>
    <td class="px-4 py-2">{{ $weather_log->condition }}</td>
    <td class="px-4 py-2">{{ $weather_log->description }}</td>
    <td class="px-4 py-2 text-right">
        <form class="weather-delete-form" action="{{ route('daily_reports.weather.destroy', $weather_log->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-500 hover:underline text-xs">Hapus</button>
        </form>
    </td>
</tr>