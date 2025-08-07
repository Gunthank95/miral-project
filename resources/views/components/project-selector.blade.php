<form action="{{ route('project.switch') }}" method="POST" id="project-switch-form">
    @csrf
    <div class="relative">
        <label for="project-select" class="sr-only">Pilih Proyek</label>
        <select id="project-select" name="project_id" 
                onchange="document.getElementById('project-switch-form').submit()"
                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            
            @if(isset($userProjects) && $userProjects->count() > 0)
                <option value="">-- Pilih Proyek --</option>
                @foreach ($userProjects as $project)
                    <option value="{{ $project->id }}" {{ session('active_project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            @else
                <option value="">Tidak ada proyek</option>
            @endif
            
        </select>
    </div>
</form>