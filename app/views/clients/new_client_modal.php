<?php
/**
 * New Client Modal View
 * Location: app/views/clients/new_client_modal.php
 * Multi-step wizard for client creation
 */

// Ensure we have access to BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', '/smartAd');
}
?>

<div class="client-creation-wizard">
    <!-- Progress Indicator -->
    <div class="wizard-progress">
        <div class="progress-bar">
            <div class="progress-fill" id="wizardProgressFill"></div>
        </div>
    </div>

    <!-- Form Container -->
    <form id="newClientForm" class="wizard-form" novalidate autocomplete="off">
        <!-- Step 1: Basic Information -->
    <div class="wizard-step active" data-wizard-step="1">
            <div class="step-content">
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="company_name" class="form-label">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" required>
                        <div class="field-error" id="company_name_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="client_type" class="form-label">Client Type *</label>
			<select id="client_type" name="client_type" class="form-control" required>
                            <option value="">Select client type...</option>
                        </select>
                        <div class="field-error" id="client_type_error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="industry" class="form-label">Industry *</label>
                        <select id="industry" name="industry" class="form-control" required>
                            <option value="">Select industry...</option>
                        </select>
                        <div class="field-error" id="industry_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="client_description" class="form-label">Business Description</label>
                        <textarea id="client_description" name="client_description" class="form-control" rows="3" placeholder="Brief description of the business..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Contact Details -->
        <div class="wizard-step" data-wizard-step="2">
            <div class="step-content">
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="contact_person" class="form-label">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-control" required>
                        <div class="field-error" id="contact_person_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="contact_person_position" class="form-label">Position/Title</label>
                        <input type="text" id="contact_person_position" name="contact_person_position" class="form-control" placeholder="e.g., Marketing Manager">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="contact_phone" class="form-label">Phone Number *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" required placeholder="+233 XX XXX XXXX">
                        <div class="field-error" id="contact_phone_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="contact_email" class="form-label">Email Address *</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" required placeholder="contact@company.com">
                        <div class="field-error" id="contact_email_error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="alternative_contact" class="form-label">Alternative Contact</label>
                        <input type="text" id="alternative_contact" name="alternative_contact" class="form-control" placeholder="Secondary contact person">
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" id="website" name="website" class="form-control" placeholder="https://www.company.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Address & Location -->
        <div class="wizard-step" data-wizard-step="3">
            <div class="step-content">
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="country" class="form-label">Country *</label>
                        <select id="country" name="country" class="form-control" required>
                            <option value="">Select country...</option>
                        </select>
                        <div class="field-error" id="country_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="region" class="form-label">Region *</label>
                        <select id="region" name="region" class="form-control" required>
                            <option value="">Select region...</option>
                        </select>
                        <div class="field-error" id="region_error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="city" class="form-label">City *</label>
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Select city...</option>
			</select>
                        <div class="field-error" id="city_error"></div>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" placeholder="e.g., 12345">
                    </div>
		</div>
                
        <div class="form-group">
                    <label for="address" class="form-label">Physical Address *</label>
                    <textarea id="address" name="address" class="form-control" rows="2" required placeholder="Street address, building name, etc."></textarea>
                    <div class="field-error" id="address_error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="postal_address" class="form-label">Postal Address</label>
                        <input type="text" id="postal_address" name="postal_address" class="form-control" placeholder="P.O. Box...">
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="digital_address" class="form-label">Digital Address (Ghana GPS)</label>
                        <input type="text" id="digital_address" name="digital_address" class="form-control" placeholder="e.g., GD-123-4567">
                        <small class="text-muted">Optional: Ghana Digital Address or Google Plus Code</small>
                    </div>
        </div>
                
        <div class="form-group">
                    <label for="location_notes" class="form-label">Location Notes</label>
                    <textarea id="location_notes" name="location_notes" class="form-control" rows="2" placeholder="Additional location information..."></textarea>
                </div>
            </div>
        </div>

        <!-- Step 4: Credit Settings -->
        <div class="wizard-step" data-wizard-step="4">
            <div class="step-content">
                <div class="credit-info-card">
                    <h4>Credit Information</h4>
                    <p class="text-muted">Set up credit limits and payment terms for this client.</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="credit_limit" class="form-label">Credit Limit (GHS)</label>
                        <input type="number" id="credit_limit" name="credit_limit" class="form-control" min="0" step="0.01" placeholder="0.00">
                        <small class="text-muted">Leave empty for no credit limit</small>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="credit_rating" class="form-label">Credit Rating</label>
                        <select id="credit_rating" name="credit_rating" class="form-control">
                            <option value="">Select rating...</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="payment_terms" class="form-label">Payment Terms</label>
                        <select id="payment_terms" name="payment_terms" class="form-control">
                            <option value="">Select terms...</option>
                            <option value="net_15">Net 15 Days</option>
                            <option value="net_30">Net 30 Days</option>
                            <option value="net_45">Net 45 Days</option>
                            <option value="net_60">Net 60 Days</option>
                            <option value="cash_on_delivery">Cash on Delivery</option>
                            <option value="prepaid">Prepaid</option>
                        </select>
                    </div>
                    
                    <div class="form-group flex-1">
                        <label for="preferred_contact_method" class="form-label">Preferred Contact Method</label>
                        <select id="preferred_contact_method" name="preferred_contact_method" class="form-control">
                            <option value="">Select method...</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
            </select>
        </div>
    </div>

        <div class="form-group">
                    <label for="client_notes" class="form-label">Additional Notes</label>
                    <textarea id="client_notes" name="client_notes" class="form-control" rows="3" placeholder="Any additional information about this client..."></textarea>
                </div>
            </div>
        </div>

        <!-- Step 5: Review & Save -->
        <div class="wizard-step" data-wizard-step="5">
            <div class="step-content">
                <div class="review-summary">
                    <h4>Review Client Information</h4>
                    <p class="text-muted">Please review all information before creating the client.</p>
                </div>
                
                <div class="review-sections">
                    <!-- Basic Information Review -->
                    <div class="review-section">
                        <h5>Basic Information</h5>
                        <div class="review-content" id="reviewBasicInfo">
                            <!-- Will be populated by JavaScript -->
                        </div>
        </div>
                    
                    <!-- Contact Details Review -->
                    <div class="review-section">
                        <h5>Contact Details</h5>
                        <div class="review-content" id="reviewContactInfo">
                            <!-- Will be populated by JavaScript -->
        </div>
        </div>
                    
                    <!-- Address Review -->
                    <div class="review-section">
                        <h5>Address & Location</h5>
                        <div class="review-content" id="reviewAddressInfo">
                            <!-- Will be populated by JavaScript -->
        </div>
    </div>

                    <!-- Credit Information Review -->
                    <div class="review-section">
                        <h5>Credit Information</h5>
                        <div class="review-content" id="reviewCreditInfo">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
        </div>
                
                <!-- Auto-save indicator -->
                <div class="auto-save-indicator" id="autoSaveIndicator" style="display: none;">
                    <i class="fas fa-save"></i>
                    <span>Auto-saved</span>
        </div>
        </div>
    </div>
</form>
</div>

<!-- Include wizard-specific styles -->
<style>
/* Wizard-specific styles are included in clients.css */
.wizard-progress {
    margin-bottom: 2rem;
}

.progress-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 2px;
    transition: width 0.3s ease;
    width: 20%; /* Will be updated by JavaScript */
}

.wizard-step {
    display: none;
}

.wizard-step.active {
    display: block;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group.flex-2 {
    grid-column: span 2;
}

.form-group.flex-1 {
    grid-column: span 1;
}

.field-error {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: none;
}

.field-error.show {
    display: block;
}

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.credit-info-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.credit-info-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
}

.review-summary {
    margin-bottom: 2rem;
}

.review-summary h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
}

.review-sections {
    display: grid;
    gap: 1.5rem;
}

.review-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
}

.review-section h5 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.review-content {
    font-size: 0.875rem;
    color: #64748b;
}

.review-item {
    margin-bottom: 0.5rem;
}

.review-item strong {
    color: #374151;
}

.auto-save-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #10b981;
    font-size: 0.875rem;
    margin-top: 1rem;
    padding: 0.5rem;
    background: #f0fdf4;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-group.flex-2,
    .form-group.flex-1 {
        grid-column: span 1;
    }
}
</style>
