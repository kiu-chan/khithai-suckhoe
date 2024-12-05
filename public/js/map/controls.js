export class ControlManager {
    constructor(map, markerManager, layerManager) {
        this.map = map;
        this.markerManager = markerManager;
        this.layerManager = layerManager;
        this.setupControls();
    }

    // Thiết lập các điều khiển
    setupControls() {
        this.setupLayerControls();
        this.setupOpacityControls();
        this.setupBaseMapControl();
        this.setupSidebarControls();
    }

    // Thiết lập điều khiển layer
    setupLayerControls() {
        // AQI Station Layer
        document.getElementById('aqiStationLayer').addEventListener('change', (e) => {
            const isVisible = e.target.checked;
            this.markerManager.setMarkersVisibility('monitoring', isVisible);
        });

        // Factory Layer
        document.getElementById('factoryLayer').addEventListener('change', (e) => {
            const isVisible = e.target.checked;
            this.markerManager.setMarkersVisibility('factory', isVisible);
        });

        // Wind Layer
        document.getElementById('windLayer').addEventListener('change', (e) => {
            const isVisible = e.target.checked;
            this.markerManager.setMarkersVisibility('wind', isVisible);
        });

        // WMS Layer
        document.getElementById('wmsLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.wmsLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.wmsLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // La Hien Layer
        document.getElementById('laHienLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.laHienLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.laHienLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // La Hien Layer
        document.getElementById('laHienLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.caoNganLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.caoNganLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // La Hien Layer
        document.getElementById('laHienLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.quangSonLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.quangSonLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // La Hien Layer
        document.getElementById('laHienLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.quanTrieuLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.quanTrieuLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // La Hien Layer
        document.getElementById('laHienLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.luuXaLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.luuXaLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // Thai Nguyen Boundary Layer
        document.getElementById('thaiNguyenLayer').addEventListener('change', (e) => {
            const isVisible = e.target.checked;
            this.layerManager.thaiNguyenPolygons.forEach(item => {
                if (item.polygon) {
                    item.polygon.setVisible(isVisible);
                }
            });
        });

        document.getElementById('districtLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.districtBoundaryLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.districtBoundaryLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });

        // Commune Boundary Layer
        document.getElementById('communeLayer').addEventListener('change', (e) => {
            const index = this.map.overlayMapTypes.getArray()
                .indexOf(this.layerManager.communeBoundaryLayer);
            if (e.target.checked && index === -1) {
                this.map.overlayMapTypes.push(this.layerManager.communeBoundaryLayer);
            } else if (!e.target.checked && index !== -1) {
                this.map.overlayMapTypes.removeAt(index);
            }
        });
    }

    // Thiết lập điều khiển độ mờ
    setupOpacityControls() {
        document.getElementById('wmsOpacity').addEventListener('input', (e) => {
            this.layerManager.wmsLayer.setOpacity(e.target.value / 100);
        });

        document.getElementById('laHienOpacity').addEventListener('input', (e) => {
            this.layerManager.laHienLayer.setOpacity(e.target.value / 100);
        });

        document.getElementById('laHienOpacity').addEventListener('input', (e) => {
            this.layerManager.caoNganLayer.setOpacity(e.target.value / 100);
        });

        document.getElementById('laHienOpacity').addEventListener('input', (e) => {
            this.layerManager.quangSonLayer.setOpacity(e.target.value / 100);
        });

        document.getElementById('laHienOpacity').addEventListener('input', (e) => {
            this.layerManager.quanTrieuLayer.setOpacity(e.target.value / 100);
        });

        document.getElementById('laHienOpacity').addEventListener('input', (e) => {
            this.layerManager.luuXaLayer.setOpacity(e.target.value / 100);
        });
    }

    // Thiết lập điều khiển bản đồ nền
    setupBaseMapControl() {
        document.getElementById('baseMapSelect').addEventListener('change', (e) => {
            this.map.setMapTypeId(e.target.value);
        });
    }

    // Thiết lập điều khiển sidebar
    setupSidebarControls() {
        this.setupStationControls();
        this.setupFactoryControls();
        this.setupAreaControls();
    }

    // Thiết lập điều khiển trạm quan trắc
    setupStationControls() {
        document.querySelectorAll('.station-item').forEach(item => {
            item.addEventListener('click', () => {
                const stationId = parseInt(item.dataset.id);
                const markerData = this.markerManager.monitoringMarkers
                    .find(m => m.id === stationId);
                if (markerData) {
                    this.focusOnMarker(markerData);
                }
            });
        });
    }

    // Thiết lập điều khiển nhà máy
    setupFactoryControls() {
        document.querySelectorAll('.factory-item').forEach(item => {
            item.addEventListener('click', () => {
                const factoryId = parseInt(item.dataset.id);
                const markerData = this.markerManager.factoryMarkers
                    .find(m => m.id === factoryId);
                if (markerData) {
                    this.focusOnMarker(markerData);
                }
            });
        });
    }

    // Thiết lập điều khiển khu vực
    setupAreaControls() {
        document.querySelectorAll('.area-item').forEach(item => {
            item.addEventListener('click', () => {
                const areaId = parseInt(item.dataset.id);
                const polygonData = this.layerManager.thaiNguyenPolygons
                    .find(p => p.id === areaId);
                if (polygonData) {
                    const bounds = new google.maps.LatLngBounds();
                    polygonData.polygon.getPath().forEach(latLng => bounds.extend(latLng));
                    this.map.fitBounds(bounds);
                }
            });
        });
    }

    // Focus vào marker và mở info window
    focusOnMarker(markerData) {
        this.map.panTo(markerData.marker.getPosition());
        this.map.setZoom(15);
        if (this.markerManager.currentInfoWindow) {
            this.markerManager.currentInfoWindow.close();
        }
        markerData.infoWindow.open(this.map, markerData.marker);
        this.markerManager.currentInfoWindow = markerData.infoWindow;
    }
}