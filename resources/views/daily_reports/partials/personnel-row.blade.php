<tr class="border-t" id="personnel-{{ $personnel->id }}">
    <td class="px-2 py-1">{{ $personnel->role }}</td>
    <td class="px-2 py-1 text-center">{{ $personnel->count }}</td>
    <td class="px-2 py-1 text-right">
        <form action="{{ route('daily_reports.personnel.destroy', $personnel->id) }}" method="POST" onsubmit="return confirm('Yakin?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-500 text-xs">Hapus</button>
        </form>
    </td>
</tr>