    <div class="col-md-6">
        
        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $student->name ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone ?? '') }}">
        </div>
        <div class="mb-3">
            <label>الرقم الوطني</label>
            <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id ?? '') }}">
        </div>
        <div class="mb-3">
            <label>القراءة</label>
            <select name="qiraat" class="form-control mb-2 @error('qiraat') is-invalid @enderror">
    <option value="">اختر القراءة</option>
    @foreach(config('qiraat.types') as $qiraat)
        <option value="{{ $qiraat }}" {{ old('qiraat', $user->qiraat ?? '') == $qiraat ? 'selected' : '' }}>
            {{ $qiraat }}
        </option>
    @endforeach
</select>
@error('qiraat')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
        </div>
        <div class="mb-3">
            <label>الصورة الشخصية</label>
            <input type="file" name="profile_image" class="form-control">
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label>كلمة المرور</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $student->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">نشط؟</label>
        </div>
    </div>
</div>