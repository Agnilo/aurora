<select name="roles[]" multiple class="form-select">
    @foreach($roles as $role)
        <option value="{{ $role->name }}"
            {{ $user->hasRole($role->name) ? 'selected' : '' }}>
            {{ ucfirst($role->name) }}
        </option>
    @endforeach
</select>
