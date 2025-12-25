{{-- resources/views/components/service-tree-selector.blade.php --}}
<div class="service-tree-selector" id="serviceTreeSelector">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Каталоги</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="catalogsList">
                        @foreach($catalogs as $catalog)
                            <button type="button" class="list-group-item list-group-item-action catalog-item"
                                    data-catalog-id="{{ $catalog->id }}"
                                    onclick="loadSubcatalogs({{ $catalog->id }})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $catalog->name }}</span>
                                    <small class="text-muted">{{ $catalog->subCatalogs->count() }}</small>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Подкаталоги</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="subcatalogsList">
                        <div class="list-group-item text-muted text-center py-3">
                            Выберите каталог
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Услуги</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="servicesList">
                        <div class="list-group-item text-muted text-center py-3">
                            Выберите подкаталог
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Выбранные услуги</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelectedServices()">
                        Очистить все
                    </button>
                </div>
                <div class="card-body">
                    <div id="selectedServicesContainer" class="selected-services-container">
                        <div class="text-muted text-center py-2" id="emptySelectedMessage">
                            Услуги не выбраны
                        </div>
                    </div>
                    <div class="mt-2">
                        <strong>Общая стоимость: <span id="totalPrice">0 ₸</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Скрытые поля для передачи выбранных услуг -->
    <div id="selectedServicesInputs"></div>
</div>

<style>
.service-tree-selector .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
}

.service-tree-selector .list-group-item:hover {
    background-color: #f8f9fa;
}

.service-tree-selector .list-group-item.active {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.selected-service-item {
    display: inline-block;
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 20px;
    padding: 5px 10px;
    margin: 2px;
    font-size: 12px;
}

.selected-service-item .remove-btn {
    margin-left: 5px;
    color: #f44336;
    cursor: pointer;
    font-weight: bold;
}

.service-checkbox {
    margin-right: 8px;
}

.service-price {
    font-weight: bold;
    color: #4caf50;
}

.card {
    height: 300px;
}

.card-body {
    overflow-y: auto;
}
</style>

<script>
let selectedServices = new Set();
let servicesData = {};
let catalogs = @json($catalogs->load('subCatalogs.services'));

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Если есть уже выбранные услуги (для редактирования)
    @if(isset($selectedServices) && $selectedServices->count() > 0)
        @foreach($selectedServices as $service)
            selectedServices.add({{ $service->id }});
            servicesData[{{ $service->id }}] = {
                id: {{ $service->id }},
                name: '{{ $service->name }}',
                price: {{ $service->price }},
                subcatalog: '{{ $service->subCatalog->name }}',
                catalog: '{{ $service->subCatalog->catalog->name }}'
            };
        @endforeach
        updateSelectedServicesDisplay();
    @endif
});

function loadSubcatalogs(catalogId) {
    // Активируем выбранный каталог
    document.querySelectorAll('.catalog-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-catalog-id="${catalogId}"]`).classList.add('active');

    // Загружаем подкаталоги
    const catalog = catalogs.find(c => c.id === catalogId);
    const subcatalosList = document.getElementById('subcatalogsList');

    if (catalog && catalog.sub_catalogs.length > 0) {
        subcatalosList.innerHTML = '';
        catalog.sub_catalogs.forEach(subcatalog => {
            const item = document.createElement('button');
            item.className = 'list-group-item list-group-item-action subcatalog-item';
            item.setAttribute('data-subcatalog-id', subcatalog.id);
            item.onclick = () => loadServices(subcatalog.id);

            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span>${subcatalog.name}</span>
                    <small class="text-muted">${subcatalog.services ? subcatalog.services.length : 0}</small>
                </div>
            `;

            subcatalosList.appendChild(item);
        });
    } else {
        subcatalosList.innerHTML = '<div class="list-group-item text-muted text-center py-3">Подкаталоги отсутствуют</div>';
    }

    // Очищаем список услуг
    document.getElementById('servicesList').innerHTML = '<div class="list-group-item text-muted text-center py-3">Выберите подкаталог</div>';
}

function loadServices(subcatalogId) {
    // Активируем выбранный подкаталог
    document.querySelectorAll('.subcatalog-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-subcatalog-id="${subcatalogId}"]`).classList.add('active');

    // Находим услуги
    let services = [];
    catalogs.forEach(catalog => {
        catalog.sub_catalogs.forEach(subcatalog => {
            if (subcatalog.id === subcatalogId && subcatalog.services) {
                services = subcatalog.services.filter(service => service.is_active);
            }
        });
    });

    const servicesList = document.getElementById('servicesList');

    if (services.length > 0) {
        servicesList.innerHTML = '';
        services.forEach(service => {
            const isSelected = selectedServices.has(service.id);
            const item = document.createElement('div');
            item.className = 'list-group-item';

            item.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input service-checkbox" type="checkbox"
                           id="service_${service.id}"
                           value="${service.id}"
                           ${isSelected ? 'checked' : ''}
                           onchange="toggleService(${service.id}, '${service.name}', ${service.price})">
                    <label class="form-check-label w-100" for="service_${service.id}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div>${service.name}</div>
                                ${service.description ? `<small class="text-muted">${service.description}</small>` : ''}
                            </div>
                            <div class="service-price">${formatPrice(service.price)}</div>
                        </div>
                    </label>
                </div>
            `;

            servicesList.appendChild(item);

            // Сохраняем данные услуги
            servicesData[service.id] = {
                id: service.id,
                name: service.name,
                price: service.price,
                description: service.description || '',
                subcatalog: subcatalog.name,
                catalog: catalog.name
            };
        });
    } else {
        servicesList.innerHTML = '<div class="list-group-item text-muted text-center py-3">Услуги отсутствуют</div>';
    }
}

function toggleService(serviceId, serviceName, servicePrice) {
    const checkbox = document.getElementById(`service_${serviceId}`);

    if (checkbox.checked) {
        selectedServices.add(serviceId);
    } else {
        selectedServices.delete(serviceId);
    }

    updateSelectedServicesDisplay();
}

function updateSelectedServicesDisplay() {
    const container = document.getElementById('selectedServicesContainer');
    const emptyMessage = document.getElementById('emptySelectedMessage');
    const totalPriceElement = document.getElementById('totalPrice');
    const inputsContainer = document.getElementById('selectedServicesInputs');

    // Очищаем контейнеры
    container.innerHTML = '';
    inputsContainer.innerHTML = '';

    if (selectedServices.size === 0) {
        container.appendChild(emptyMessage);
        totalPriceElement.textContent = '0 ₸';
        return;
    }

    let totalPrice = 0;

    selectedServices.forEach(serviceId => {
        const service = servicesData[serviceId];
        if (service) {
            // Создаем визуальный элемент
            const serviceItem = document.createElement('span');
            serviceItem.className = 'selected-service-item';
            serviceItem.innerHTML = `
                ${service.name} (${formatPrice(service.price)})
                <span class="remove-btn" onclick="removeService(${serviceId})" title="Удалить">×</span>
            `;
            container.appendChild(serviceItem);

            // Создаем скрытое поле
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'services[]';
            hiddenInput.value = serviceId;
            inputsContainer.appendChild(hiddenInput);

            totalPrice += parseFloat(service.price);
        }
    });

    totalPriceElement.textContent = formatPrice(totalPrice);
}

function removeService(serviceId) {
    selectedServices.delete(serviceId);

    // Снимаем галочку если услуга видна
    const checkbox = document.getElementById(`service_${serviceId}`);
    if (checkbox) {
        checkbox.checked = false;
    }

    updateSelectedServicesDisplay();
}

function clearSelectedServices() {
    selectedServices.clear();

    // Снимаем все галочки
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });

    updateSelectedServicesDisplay();
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price) + ' ₸';
}

// Функция для получения выбранных услуг (для внешнего использования)
function getSelectedServices() {
    return Array.from(selectedServices);
}

// Функция для установки выбранных услуг (для внешнего использования)
function setSelectedServices(serviceIds) {
    selectedServices.clear();
    serviceIds.forEach(id => selectedServices.add(parseInt(id)));
    updateSelectedServicesDisplay();

    // Обновляем галочки если они видны
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        const serviceId = parseInt(checkbox.value);
        checkbox.checked = selectedServices.has(serviceId);
    });
}
</script>
