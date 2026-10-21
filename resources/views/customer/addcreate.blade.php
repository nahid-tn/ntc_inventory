<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Customer New') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{route('customer.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="customerName">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" aria-describedby="emailHelp" placeholder="Enter Customer Name:" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Mobile Number</label>
                                <input type="text" class="form-control" name="mobile" placeholder="Enter Customer's Mobile Number" required pattern="[0-9]{11}" minlength="11" maxlength="11">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" placeholder="Enter Customer's Address" rows="2" oninput="autoResize(this)" style="resize: none; overflow: hidden;" required></textarea>
                        </div>

                        <!-- ছবি আপলোড সেকশন -->
                        <div class="form-group mt-3">
                            <label>Customer Photo</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customerPhoto" name="customer_photo" accept="image/*" onchange="previewImage(event)">
                                        <label class="custom-file-label" for="customerPhoto">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-info" onclick="captureFromCamera()">
                                        <i class="fas fa-camera"></i> Take Photo from Camera
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ছবি প্রিভিউ সেকশন -->
                        <div class="form-group mt-3">
                            <div id="imagePreviewContainer" style="display: none;">
                                <label>Photo Preview</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <img id="imagePreview" src="#" alt="Customer Photo" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                    </div>
                                    <div class="col-md-8">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                            <i class="fas fa-times"></i> Remove Photo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ক্যামেরা ইনপুট (লুকানো) -->
                        <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;" onchange="handleCameraCapture(event)">

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button> | 
                            <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
</x-app-layout>

<script>
// টেক্সট এরিয়া অটো রিসাইজ
function autoResize(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";
}

// ফাইল সিলেক্ট করলে প্রিভিউ দেখানো
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        document.getElementById('imagePreviewContainer').style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
    
    // ফাইলের নাম দেখানো
    const fileName = event.target.files[0].name;
    const label = document.querySelector('.custom-file-label');
    label.textContent = fileName;
}

// ক্যামেরা থেকে ছবি তোলা
function captureFromCamera() {
    // মোবাইল ডিভাইস চেক
    if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        document.getElementById('cameraInput').click();
    } else {
        // ডেস্কটপে ক্যামেরা সাপোর্ট নেই
        alert('Camera is only supported on mobile devices. Please use the file upload option.');
        // বিকল্প হিসেবে ফাইল আপলোড খোলা
        document.getElementById('customerPhoto').click();
    }
}

// ক্যামেরা থেকে ক্যাপচার করা ছবি হ্যান্ডেল
function handleCameraCapture(event) {
    const file = event.target.files[0];
    if (file) {
        // ফাইল ইনপুটে সেট করা
        const fileInput = document.getElementById('customerPhoto');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        // প্রিভিউ দেখানো
        const reader = new FileReader();
        reader.onload = function(e){
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
            
            // ফাইলের নাম আপডেট
            const label = document.querySelector('.custom-file-label');
            label.textContent = file.name;
        }
        reader.readAsDataURL(file);
    }
}

// ছবি রিমুভ করা
function removeImage() {
    document.getElementById('imagePreview').src = '#';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('customerPhoto').value = '';
    document.getElementById('cameraInput').value = '';
    const label = document.querySelector('.custom-file-label');
    label.textContent = 'Choose file';
}

// ফাইল সাইজ চেক
document.getElementById('customerPhoto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // 2MB = 2 * 1024 * 1024
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            this.value = '';
            removeImage();
            return;
        }
        
        // ফাইলের টাইপ চেক
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPG, PNG, GIF)');
            this.value = '';
            removeImage();
            return;
        }
    }
});
</script>

<!-- CSS স্টাইল -->
<style>
.custom-file {
    position: relative;
    display: inline-block;
    width: 100%;
    height: calc(2.25rem + 2px);
}

.custom-file-input {
    position: relative;
    z-index: 2;
    width: 100%;
    height: calc(2.25rem + 2px);
    margin: 0;
    opacity: 0;
}

.custom-file-label {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1;
    height: calc(2.25rem + 2px);
    padding: .375rem .75rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-file-label::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 3;
    display: block;
    height: calc(2.25rem + 2px);
    padding: .375rem .75rem;
    line-height: 1.5;
    color: #495057;
    content: "Browse";
    background-color: #e9ecef;
    border-left: 1px solid #ced4da;
    border-radius: 0 .25rem .25rem 0;
}

.btn-info {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-sm {
    padding: .25rem .5rem;
    font-size: .875rem;
    line-height: 1.5;
    border-radius: .2rem;
}

.mt-3 {
    margin-top: 1rem;
}
</style>