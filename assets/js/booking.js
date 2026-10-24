/**
 * GlobeTrek Adventures - Dynamic Booking & Customization Engine
 * Handles live price calculation, add-on toggling, and multi-step validation.
 */

document.addEventListener('DOMContentLoaded', () => {
  const bookingForm = document.getElementById('bookingWizardForm');
  if (!bookingForm) return;

  // Configuration passed from backend
  const basePricePerPerson = parseFloat(bookingForm.dataset.basePrice || 0);
  const durationDays = parseInt(bookingForm.dataset.durationDays || 3);
  const durationNights = parseInt(bookingForm.dataset.durationNights || 2);

  const travelersInput = document.getElementById('travelersCount');
  const hotelRadios = document.querySelectorAll('input[name="accommodation_id"]');
  const transportRadios = document.querySelectorAll('input[name="transport_id"]');
  const addonCheckboxes = document.querySelectorAll('input[name="addons[]"]');

  // Summary Elements
  const summaryTravelersEl = document.getElementById('sumTravelers');
  const summaryPackageTotalEl = document.getElementById('sumPackageTotal');
  const summaryHotelTotalEl = document.getElementById('sumHotelTotal');
  const summaryTransportTotalEl = document.getElementById('sumTransportTotal');
  const summaryAddonsTotalEl = document.getElementById('sumAddonsTotal');
  const summaryGrandTotalEl = document.getElementById('sumGrandTotal');

  function calculateBookingCost() {
    const travelers = Math.max(1, parseInt(travelersInput ? travelersInput.value : 1) || 1);
    
    // 1. Package base price total
    const packageTotal = basePricePerPerson * travelers;

    // 2. Hotel total
    let hotelRate = 0;
    let selectedHotelName = 'Standard Lodge (Included)';
    hotelRadios.forEach(radio => {
      if (radio.checked) {
        hotelRate = parseFloat(radio.dataset.price || 0);
        selectedHotelName = radio.dataset.name || selectedHotelName;
        // visual selection highlight
        const parentCard = radio.closest('.option-select-card');
        if (parentCard) {
          document.querySelectorAll('.hotel-card').forEach(c => c.classList.remove('selected'));
          parentCard.classList.add('selected');
        }
      }
    });
    const hotelTotal = hotelRate * durationNights;

    // 3. Transport total
    let transportRate = 0;
    let selectedTransportName = 'Standard';
    transportRadios.forEach(radio => {
      if (radio.checked) {
        transportRate = parseFloat(radio.dataset.price || 0);
        selectedTransportName = radio.dataset.name || selectedTransportName;
        // visual selection highlight
        const parentCard = radio.closest('.option-select-card');
        if (parentCard) {
          document.querySelectorAll('.transport-card').forEach(c => c.classList.remove('selected'));
          parentCard.classList.add('selected');
        }
      }
    });
    const transportTotal = transportRate * durationDays;

    // 4. Addons total
    let addonsTotal = 0;
    addonCheckboxes.forEach(cb => {
      const parentCard = cb.closest('.option-select-card');
      if (cb.checked) {
        addonsTotal += parseFloat(cb.dataset.price || 0);
        if (parentCard) parentCard.classList.add('selected');
      } else {
        if (parentCard) parentCard.classList.remove('selected');
      }
    });

    const grandTotal = packageTotal + hotelTotal + transportTotal + addonsTotal;

    // Update DOM
    if (summaryTravelersEl) summaryTravelersEl.textContent = `${travelers} Person(s)`;
    if (summaryPackageTotalEl) summaryPackageTotalEl.textContent = `$${packageTotal.toFixed(2)}`;
    if (summaryHotelTotalEl) summaryHotelTotalEl.textContent = `$${hotelTotal.toFixed(2)}`;
    if (summaryTransportTotalEl) summaryTransportTotalEl.textContent = `$${transportTotal.toFixed(2)}`;
    if (summaryAddonsTotalEl) summaryAddonsTotalEl.textContent = `$${addonsTotal.toFixed(2)}`;
    if (summaryGrandTotalEl) summaryGrandTotalEl.textContent = `$${grandTotal.toFixed(2)}`;
  }

  // Bind Listeners
  if (travelersInput) travelersInput.addEventListener('input', calculateBookingCost);
  hotelRadios.forEach(r => r.addEventListener('change', calculateBookingCost));
  transportRadios.forEach(r => r.addEventListener('change', calculateBookingCost));
  addonCheckboxes.forEach(cb => cb.addEventListener('change', calculateBookingCost));

  // Initial Calculation on load
  calculateBookingCost();

  // Wizard Step Switching
  const stepPanels = document.querySelectorAll('.wizard-step-panel');
  const stepIndicators = document.querySelectorAll('.wizard-step-item');
  let currentStep = 1;

  window.goToBookingStep = function(targetStep) {
    if (targetStep < 1 || targetStep > stepPanels.length) return;

    // Validate step 1 before proceeding
    if (targetStep > 1) {
      const travelDate = document.getElementById('travelDate');
      if (travelDate && !travelDate.value) {
        alert('Please select your preferred travel start date.');
        travelDate.focus();
        return;
      }
    }

    // Validate step 3 before proceeding to payment
    if (targetStep === 4) {
      const guestName = document.getElementById('guestFullName');
      const guestEmail = document.getElementById('guestEmail');
      if ((guestName && !guestName.value.trim()) || (guestEmail && !guestEmail.value.trim())) {
        alert('Please provide traveler contact details before continuing to payment.');
        if (guestName) guestName.focus();
        return;
      }
    }

    currentStep = targetStep;

    // Toggle panels
    stepPanels.forEach(panel => {
      const stepNum = parseInt(panel.dataset.step);
      panel.style.display = (stepNum === currentStep) ? 'block' : 'none';
    });

    // Update Indicators
    stepIndicators.forEach(ind => {
      const indNum = parseInt(ind.dataset.step);
      ind.classList.remove('active', 'completed');
      if (indNum === currentStep) {
        ind.classList.add('active');
      } else if (indNum < currentStep) {
        ind.classList.add('completed');
      }
    });

    window.scrollTo({ top: 100, behavior: 'smooth' });
  };
});
