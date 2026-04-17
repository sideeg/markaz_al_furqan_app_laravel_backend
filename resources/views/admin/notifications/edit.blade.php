{{-- Path: resources/views/admin/notifications/edit.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-2" style="color: #D4A843;"></i>
            <span style="color: #0B1120;">العودة للإشعارات</span>
        </a>
        <h1 class="h3 mt-3 mb-0" style="color: #0B1120;">
            <i class="fas fa-edit me-2" style="color: #D4A843;"></i> تعديل الإشعار
        </h1>
        <p class="text-muted mt-2">تحديث بيانات الإشعار أو إرساله الآن</p>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm" style="border-top: 4px solid #D4A843;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.notifications.update', $notification) }}" 
                          method="POST" 
                          id="editNotificationForm">
                        @csrf
                        @method('PUT')

                        <!-- Title Field -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold" style="color: #0B1120;">
                                <i class="fas fa-heading me-2" style="color: #D4A843;"></i> عنوان الإشعار
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $notification->title) }}"
                                   placeholder="أدخل عنوان الإشعار"
                                   maxlength="255"
                                   required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">سيظهر هذا العنوان في رأس الإشعار</small>
                        </div>

                        <!-- Message Field -->
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold" style="color: #0B1120;">
                                <i class="fas fa-message me-2" style="color: #D4A843;"></i> محتوى الإشعار
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="4"
                                      placeholder="أدخل نص الإشعار"
                                      maxlength="1000"
                                      required>{{ old('message', $notification->message) }}</textarea>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                <span id="charCount">{{ strlen($notification->message) }}</span> / 1000 حرف
                            </small>
                        </div>

                        <!-- Type Field -->
                        <div class="mb-4">
                            <label for="type" class="form-label fw-bold" style="color: #0B1120;">
                                <i class="fas fa-tag me-2" style="color: #D4A843;"></i> نوع الإشعار
                            </label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type"
                                    required>
                                <option value="">-- اختر نوع الإشعار --</option>
                                <option value="custom_broadcast" 
                                    {{ old('type', $notification->type) == 'custom_broadcast' ? 'selected' : '' }}>
                                    <i class="fas fa-bullhorn me-2"></i> إشعار مخصص (بث عام)
                                </option>
                                <option value="enrollment" 
                                    {{ old('type', $notification->type) == 'enrollment' ? 'selected' : '' }}>
                                    <i class="fas fa-user-check me-2"></i> قبول الالتحاق
                                </option>
                                <option value="course_start" 
                                    {{ old('type', $notification->type) == 'course_start' ? 'selected' : '' }}>
                                    <i class="fas fa-play-circle me-2"></i> بداية الدورة
                                </option>
                                <option value="course_end" 
                                    {{ old('type', $notification->type) == 'course_end' ? 'selected' : '' }}>
                                    <i class="fas fa-stop-circle me-2"></i> نهاية الدورة
                                </option>
                                <option value="new_student" 
                                    {{ old('type', $notification->type) == 'new_student' ? 'selected' : '' }}>
                                    <i class="fas fa-user-plus me-2"></i> طالب جديد
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Target Field -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: #0B1120;">
                                <i class="fas fa-users me-2" style="color: #D4A843;"></i> المتلقون
                            </label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="target" 
                                               id="target_students" 
                                               value="students"
                                               {{ old('target', $notification->target) == 'students' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="target_students">
                                            <i class="fas fa-users me-1" style="color: #2E7D32;"></i> الطلاب فقط
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="target" 
                                               id="target_teachers" 
                                               value="teachers"
                                               {{ old('target', $notification->target) == 'teachers' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="target_teachers">
                                            <i class="fas fa-chalkboard-user me-1" style="color: #1565C0;"></i> المشايخ والمعلمون
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="target" 
                                               id="target_both" 
                                               value="both"
                                               {{ old('target', $notification->target) == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="target_both">
                                            <i class="fas fa-globe me-1" style="color: #D4A843;"></i> الجميع
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('target')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status & Send Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="is_active" 
                                           id="is_active"
                                           value="1"
                                           {{ old('is_active', $notification->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <i class="fas fa-check-circle me-2" style="color: #2E7D32;"></i> تفعيل الإشعار
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">الإشعارات المعطلة لن تظهر للمستخدمين</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="send_now" 
                                           id="send_now"
                                           value="1"
                                           {{ old('send_now') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_now">
                                        <i class="fas fa-paper-plane me-2" style="color: #1565C0;"></i> إرسال فوراً
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">إرسال الإشعار لجميع المستقبلين المستهدفين</small>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div id="sendInfo" class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>ملاحظة:</strong> إذا اخترت "إرسال فوراً"، سيتم إرسال الإشعار لجميع المستقبلين ولا يمكن تعديله بعد ذلك.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <!-- Submission Info -->
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>تلميح:</strong> يمكنك حفظ التغييرات كمسودة دون إرسال، أو تحديث وإرسال الإشعار مباشرة.
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> إلغاء
                            </a>
                            <button type="submit" 
                                    name="action" 
                                    value="save"
                                    class="btn" 
                                    style="background: linear-gradient(135deg, #F57F17, #E65100); color: white;">
                                <i class="fas fa-save me-2"></i> حفظ التغييرات
                            </button>
                            <button type="submit" 
                                    name="action" 
                                    value="send"
                                    class="btn" 
                                    style="background: linear-gradient(135deg, #D4A843, #B8860B); color: white;"
                                    onclick="return confirm('هل أنت متأكد من إرسال هذا الإشعار؟')">
                                <i class="fas fa-paper-plane me-2"></i> حفظ وإرسال
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- History Info -->
            <div class="card border-0 shadow-sm mt-4" style="border-top: 4px solid #1565C0;">
                <div class="card-body">
                    <h6 class="mb-3" style="color: #0B1120;">
                        <i class="fas fa-history me-2" style="color: #1565C0;"></i> معلومات المسودة
                    </h6>
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <div><strong>تاريخ الإنشاء:</strong> {{ $notification->created_at->format('Y-m-d H:i:s') }}</div>
                            <div><strong>آخر تعديل:</strong> {{ $notification->updated_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>حالة الإرسال:</strong> <span class="badge bg-warning text-dark">مسودة</span></div>
                            <div><strong>الحالة:</strong> 
                                @if ($notification->is_active)
                                    <span class="badge bg-success">مفعل</span>
                                @else
                                    <span class="badge bg-secondary">معطل</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus,
    .form-select:focus {
        border-color: #D4A843;
        box-shadow: 0 0 0 0.2rem rgba(212, 168, 67, 0.25);
    }
    
    .form-check-input:checked {
        background-color: #D4A843;
        border-color: #D4A843;
    }
    
    .form-check-input:focus {
        border-color: #D4A843;
        box-shadow: 0 0 0 0.25rem rgba(212, 168, 67, 0.25);
    }
</style>

<script>
// Update character count
document.getElementById('message').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Handle send_now checkbox
document.getElementById('send_now').addEventListener('change', function() {
    const sendInfo = document.getElementById('sendInfo');
    if (this.checked) {
        sendInfo.classList.remove('alert-warning');
        sendInfo.classList.add('alert-danger');
        sendInfo.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>تحذير:</strong> سيتم إرسال الإشعار لجميع المستقبلين ولا يمكن تعديله بعد الإرسال.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
    } else {
        sendInfo.classList.remove('alert-danger');
        sendInfo.classList.add('alert-warning');
        sendInfo.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            <strong>ملاحظة:</strong> إذا اخترت "إرسال فوراً"، سيتم إرسال الإشعار لجميع المستقبلين ولا يمكن تعديله بعد ذلك.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
    }
});
</script>
@endsection