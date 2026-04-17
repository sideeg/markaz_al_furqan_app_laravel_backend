@extends('layouts.admin')

@section('title', 'إرسال إشعار جديد')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1 fw-bold">إرسال إشعار جديد</h1>
                    <p class="text-muted mb-0">
                        أرسل إشعارًا يدويًا إلى الطلاب أو المشايخ أو الجميع من نفس الصفحة.
                    </p>
                </div>

                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                    العودة إلى قائمة الإشعارات
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-2">يوجد خطأ أو أكثر، يرجى المراجعة:</div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h2 class="h5 mb-0">بيانات الإشعار</h2>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('admin.notifications.store') }}" method="POST" id="notificationForm">
                                @csrf

                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold">عنوان الإشعار</label>
                                    <input
                                        type="text"
                                        name="title"
                                        id="title"
                                        value="{{ old('title') }}"
                                        class="form-control form-control-lg @error('title') is-invalid @enderror"
                                        placeholder="مثال: بداية الدورة غدًا"
                                        maxlength="255"
                                        required
                                    >
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label fw-semibold">نص الإشعار</label>
                                    <textarea
                                        name="message"
                                        id="message"
                                        rows="6"
                                        class="form-control @error('message') is-invalid @enderror"
                                        placeholder="اكتب الرسالة بشكل واضح ومباشر..."
                                        required
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        اجعل الرسالة قصيرة وواضحة، واذكر اسم الدورة أو المجموعة عند الحاجة.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold d-block mb-2">إرسال إلى</label>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="target"
                                                id="target_students"
                                                value="students"
                                                {{ old('target', 'students') === 'students' ? 'checked' : '' }}
                                                autocomplete="off"
                                                required
                                            >
                                            <label class="btn btn-outline-primary w-100 py-3 h-100 text-start" for="target_students">
                                                <div class="fw-bold">الطلاب</div>
                                                <small class="text-muted d-block mt-1">إرسال إلى جميع الطلاب فقط</small>
                                            </label>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="target"
                                                id="target_teachers"
                                                value="teachers"
                                                {{ old('target') === 'teachers' ? 'checked' : '' }}
                                                autocomplete="off"
                                            >
                                            <label class="btn btn-outline-primary w-100 py-3 h-100 text-start" for="target_teachers">
                                                <div class="fw-bold">المشايخ / المعلمون</div>
                                                <small class="text-muted d-block mt-1">إرسال إلى الشيوخ والمعلمين فقط</small>
                                            </label>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <input
                                                type="radio"
                                                class="btn-check"
                                                name="target"
                                                id="target_both"
                                                value="both"
                                                {{ old('target') === 'both' ? 'checked' : '' }}
                                                autocomplete="off"
                                            >
                                            <label class="btn btn-outline-primary w-100 py-3 h-100 text-start" for="target_both">
                                                <div class="fw-bold">الجميع</div>
                                                <small class="text-muted d-block mt-1">الطلاب والمشايخ معًا</small>
                                            </label>
                                        </div>
                                    </div>

                                    @error('target')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">
                                        إلغاء
                                    </a>

                                    <button
                                        type="submit"
                                        class="btn btn-primary px-4"
                                        onclick="return confirm('هل أنت متأكد من إرسال هذا الإشعار؟')"
                                    >
                                        إرسال الإشعار الآن
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h2 class="h6 mb-0">معاينة مباشرة</h2>
                        </div>
                        <div class="card-body">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-2" id="previewTarget">إلى: الطلاب</div>
                                <div class="fw-bold mb-2" id="previewTitle">عنوان الإشعار سيظهر هنا</div>
                                <div class="text-muted small lh-lg" id="previewMessage">
                                    نص الإشعار سيظهر هنا عند الكتابة.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h2 class="h6 mb-0">نصائح سريعة</h2>
                        </div>
                        <div class="card-body">
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-2">استخدم عنوانًا واضحًا ومباشرًا.</li>
                                <li class="mb-2">ضع أهم المعلومة في أول سطر من الرسالة.</li>
                                <li class="mb-2">اختر الجهة المستهدفة بدقة لتجنب الإرسال غير الضروري.</li>
                                <li>يمكنك لاحقًا إضافة إشعارات مجدولة أو خاصة بدورة معينة.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const title = document.getElementById('title');
    const message = document.getElementById('message');
    const targetInputs = document.querySelectorAll('input[name="target"]');

    const previewTitle = document.getElementById('previewTitle');
    const previewMessage = document.getElementById('previewMessage');
    const previewTarget = document.getElementById('previewTarget');

    const targetLabels = {
        students: 'إلى: الطلاب',
        teachers: 'إلى: المشايخ / المعلمون',
        both: 'إلى: الجميع'
    };

    function refreshPreview() {
        previewTitle.textContent = title.value.trim() || 'عنوان الإشعار سيظهر هنا';
        previewMessage.textContent = message.value.trim() || 'نص الإشعار سيظهر هنا عند الكتابة.';

        const selectedTarget = document.querySelector('input[name="target"]:checked');
        previewTarget.textContent = targetLabels[selectedTarget ? selectedTarget.value : 'students'];
    }

    title.addEventListener('input', refreshPreview);
    message.addEventListener('input', refreshPreview);
    targetInputs.forEach(input => input.addEventListener('change', refreshPreview));

    refreshPreview();
})();
</script>
@endsection