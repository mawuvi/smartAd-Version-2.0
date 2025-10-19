/**
 * Document Upload Module
 * Location: public/js/modules/documentUploadModule.js
 * Drag-and-drop file upload with validation
 */

(function() {
    'use strict';

    // Document Upload Module API
    window.DocumentUploadModule = {
        initializeDropZone: initializeDropZone,
        uploadFiles: uploadFiles,
        getUploadedFiles: getUploadedFiles
    };

    let uploadedFiles = [];

    // --- Initialization ---

    function initializeDropZone(dropZoneId, options = {}) {
        const dropZone = document.getElementById(dropZoneId);
        if (!dropZone) {
            console.error(`Drop zone element not found: ${dropZoneId}`);
            return;
        }

        const config = {
            maxFiles: options.maxFiles || 5,
            maxFileSize: options.maxFileSize || 10 * 1024 * 1024, // 10MB default
            allowedTypes: options.allowedTypes || ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            onFileAdded: options.onFileAdded || null,
            onFileRemoved: options.onFileRemoved || null,
            onUploadComplete: options.onUploadComplete || null
        };

        // Create upload UI
        createUploadUI(dropZone, config);

        // Setup drag and drop listeners
        setupDragAndDrop(dropZone, config);

        // Setup file input
        setupFileInput(dropZone, config);
    }

    function createUploadUI(dropZone, config) {
        dropZone.innerHTML = `
            <div class="upload-container">
                <div class="upload-drop-zone" id="dropArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h4>Drag & Drop Files Here</h4>
                    <p>or</p>
                    <button type="button" class="btn btn-primary" id="selectFilesBtn">
                        <i class="fas fa-file"></i> Select Files
                    </button>
                    <p class="upload-hint">
                        Accepted: Images, PDF, Word documents<br>
                        Max file size: ${formatFileSize(config.maxFileSize)}<br>
                        Max ${config.maxFiles} files
                    </p>
                    <input type="file" id="fileInput" multiple accept="${config.allowedTypes.join(',')}" style="display: none;">
                </div>
                <div class="upload-files-list" id="filesList"></div>
            </div>

            <style>
            .upload-container {
                margin: 1rem 0;
            }

            .upload-drop-zone {
                border: 2px dashed #cbd5e1;
                border-radius: 8px;
                padding: 2rem;
                text-align: center;
                background: #f8fafc;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .upload-drop-zone:hover,
            .upload-drop-zone.drag-over {
                border-color: #3b82f6;
                background: #eff6ff;
            }

            .upload-icon {
                font-size: 3rem;
                color: #94a3b8;
                margin-bottom: 1rem;
            }

            .upload-drop-zone h4 {
                margin: 0 0 0.5rem 0;
                color: #1e293b;
            }

            .upload-drop-zone p {
                margin: 0.5rem 0;
                color: #64748b;
            }

            .upload-hint {
                font-size: 0.75rem;
                margin-top: 1rem !important;
            }

            .upload-files-list {
                margin-top: 1rem;
                max-height: 300px;
                overflow-y: auto;
            }

            .file-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.75rem;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                margin-bottom: 0.5rem;
            }

            .file-info {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex: 1;
            }

            .file-icon {
                font-size: 1.5rem;
                color: #3b82f6;
            }

            .file-details {
                flex: 1;
            }

            .file-name {
                font-weight: 500;
                color: #1e293b;
                font-size: 0.875rem;
            }

            .file-size {
                font-size: 0.75rem;
                color: #64748b;
            }

            .file-actions {
                display: flex;
                gap: 0.5rem;
            }

            .file-remove-btn {
                padding: 0.25rem 0.5rem;
                background: #fee2e2;
                color: #dc2626;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.75rem;
                transition: background 0.2s ease;
            }

            .file-remove-btn:hover {
                background: #fecaca;
            }

            .upload-progress {
                margin-top: 0.5rem;
                height: 4px;
                background: #e2e8f0;
                border-radius: 2px;
                overflow: hidden;
            }

            .upload-progress-bar {
                height: 100%;
                background: #3b82f6;
                transition: width 0.3s ease;
            }

            .file-error {
                color: #dc2626;
                font-size: 0.75rem;
                margin-top: 0.25rem;
            }
            </style>
        `;
    }

    function setupDragAndDrop(dropZone, config) {
        const dropArea = dropZone.querySelector('#dropArea');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('drag-over');
            });
        });

        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files, config, dropZone);
        });
    }

    function setupFileInput(dropZone, config) {
        const selectBtn = dropZone.querySelector('#selectFilesBtn');
        const fileInput = dropZone.querySelector('#fileInput');

        selectBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            handleFiles(files, config, dropZone);
            fileInput.value = ''; // Reset input
        });
    }

    function handleFiles(files, config, dropZone) {
        const fileArray = Array.from(files);
        const filesList = dropZone.querySelector('#filesList');

        // Check max files limit
        if (uploadedFiles.length + fileArray.length > config.maxFiles) {
            Modal.warning('Too Many Files', `Maximum ${config.maxFiles} files allowed`);
            return;
        }

        fileArray.forEach(file => {
            // Validate file
            const validation = validateFile(file, config);
            if (!validation.valid) {
                Modal.error('Invalid File', validation.error);
                return;
            }

            // Add to uploaded files
            const fileId = `file_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            const fileData = {
                id: fileId,
                file: file,
                name: file.name,
                size: file.size,
                type: file.type
            };

            uploadedFiles.push(fileData);

            // Display file
            displayFile(fileData, filesList, config);

            // Callback
            if (config.onFileAdded) {
                config.onFileAdded(fileData);
            }
        });
    }

    function validateFile(file, config) {
        // Check file type
        if (!config.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                error: `File type not allowed: ${file.name}`
            };
        }

        // Check file size
        if (file.size > config.maxFileSize) {
            return {
                valid: false,
                error: `File too large: ${file.name} (max ${formatFileSize(config.maxFileSize)})`
            };
        }

        return { valid: true };
    }

    function displayFile(fileData, filesList, config) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.id = fileData.id;

        fileItem.innerHTML = `
            <div class="file-info">
                <div class="file-icon">
                    <i class="${getFileIcon(fileData.type)}"></i>
                </div>
                <div class="file-details">
                    <div class="file-name">${fileData.name}</div>
                    <div class="file-size">${formatFileSize(fileData.size)}</div>
                </div>
            </div>
            <div class="file-actions">
                <button type="button" class="file-remove-btn" data-file-id="${fileData.id}">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        `;

        filesList.appendChild(fileItem);

        // Setup remove button
        const removeBtn = fileItem.querySelector('.file-remove-btn');
        removeBtn.addEventListener('click', () => {
            removeFile(fileData.id, config);
        });
    }

    function removeFile(fileId, config) {
        // Remove from array
        const index = uploadedFiles.findIndex(f => f.id === fileId);
        if (index > -1) {
            const fileData = uploadedFiles[index];
            uploadedFiles.splice(index, 1);

            // Remove from DOM
            const fileItem = document.getElementById(fileId);
            if (fileItem) {
                fileItem.remove();
            }

            // Callback
            if (config.onFileRemoved) {
                config.onFileRemoved(fileData);
            }
        }
    }

    async function uploadFiles(bookingId) {
        if (uploadedFiles.length === 0) {
            return { success: true, files: [] };
        }

        try {
            const formData = new FormData();
            formData.append('booking_id', bookingId);

            uploadedFiles.forEach((fileData, index) => {
                formData.append(`files[${index}]`, fileData.file);
            });

            const response = await fetch(`${window.baseUrl}/api/booking_api.php?action=upload_documents`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                uploadedFiles = []; // Clear uploaded files
                return result;
            } else {
                throw new Error(result.message || 'Upload failed');
            }

        } catch (error) {
            console.error('File upload error:', error);
            throw error;
        }
    }

    function getUploadedFiles() {
        return uploadedFiles;
    }

    // --- Utility Functions ---

    function getFileIcon(type) {
        if (type.startsWith('image/')) return 'fas fa-file-image';
        if (type === 'application/pdf') return 'fas fa-file-pdf';
        if (type.includes('word')) return 'fas fa-file-word';
        if (type.includes('excel') || type.includes('spreadsheet')) return 'fas fa-file-excel';
        return 'fas fa-file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

})();
