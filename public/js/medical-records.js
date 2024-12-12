// public/js/medical-records.js
document.addEventListener('DOMContentLoaded', function() {
    const illnessSelect = document.querySelector('select[name="illness"]');
    const filterButton = document.querySelector('button[type="submit"]');
    const cancelButton = document.querySelector('a[href*="medical-records"]');
    const tableBody = document.querySelector('table tbody');

    // Xử lý sự kiện click filter
    filterButton.addEventListener('click', function(e) {
        e.preventDefault();
        filterRecords();
    });

    // Xử lý sự kiện cancel
    cancelButton.addEventListener('click', function(e) {
        e.preventDefault();
        illnessSelect.value = '';
        filterRecords();
    });

    function filterRecords() {
        const illness = illnessSelect.value;
        
        fetch(`/medical-records?illness=${illness}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateTable(data.data);
            updateUrl(illness);
        });
    }

    function updateTable(records) {
        tableBody.innerHTML = '';
        
        records.forEach((record, index) => {
            const row = `
                <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}">
                    <td class="border px-4 py-2">${index + 1}</td>
                    <td class="border px-4 py-2">${record.patient_name}</td>
                    <td class="border px-4 py-2">${record.date_of_birth}</td>
                    <td class="border px-4 py-2">${record.checkup_date}</td>
                    <td class="border px-4 py-2">${record.address}</td>
                    <td class="border px-4 py-2">${record.illness}</td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    function updateUrl(illness) {
        const url = new URL(window.location);
        if (illness) {
            url.searchParams.set('illness', illness);
        } else {
            url.searchParams.delete('illness');
        }
        window.history.pushState({}, '', url);
    }
});