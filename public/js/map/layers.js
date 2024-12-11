export class LayerManager {
    constructor(map) {
        this.map = map;
        this.wmsLayer = null;
        this.laHienLayer = null;
        this.caoNganLayer = null;
        this.quangSonLayer = null;
        this.quanTrieuLayer = null;
        this.luuXaLayer = null;
        this.districtBoundaryLayer = null;
        this.communeBoundaryLayer = null;
        this.thaiNguyenPolygons = [];
        this.currentMode = 'current';
        this.currentForecastDay = 0;
        this.currentForecastHour = "00";
        this.factoryLayers = ['cao_ngan', 'la_hien', 'quang_son', 'quan_trieu', 'luu_xa'];
    }

    initWMSLayer() {
        this.districtBoundaryLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'hc_huyen'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'districtWMS'
        });

        this.communeBoundaryLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'hc_xa'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'communeWMS'
        });

        this.wmsLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'air_cement'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'aqiWMS'
        });

        this.laHienLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'la_hien_p'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'laHienWMS'
        });

        this.caoNganLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'cao_ngan_p'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'caoNganWMS'
        });

        this.quangSonLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'quang_son_p'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'quangSonWMS'
        });

        this.quanTrieuLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'quan_trieu_p'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'quanTrieuWMS'
        });

        this.luuXaLayer = new google.maps.ImageMapType({
            getTileUrl: (coord, zoom) => this.getWMSTileUrl(coord, zoom, 'luu_xa_p'),
            tileSize: new google.maps.Size(256, 256),
            isPng: true,
            opacity: 0.7,
            name: 'luuXaWMS'
        });

        this.showCurrentLayers();
    }

    getWMSTileUrl(coord, zoom, layer) {
        const proj = this.map.getProjection();
        const zfactor = Math.pow(2, zoom);
        const top = proj.fromPointToLatLng(
            new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor)
        );
        const bot = proj.fromPointToLatLng(
            new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor)
        );
        const bbox = [top.lng(), bot.lat(), bot.lng(), top.lat()].join(',');

        return `http://geoserver.tuaf.edu.vn/mt_thainguyen/wms?service=WMS&version=1.1.0&request=GetMap&layers=mt_thainguyen:${layer}&styles=&bbox=${bbox}&width=256&height=256&srs=EPSG:4326&format=image/png&transparent=true`;
    }

    getForecastWMSTileUrl(coord, zoom, factory, day, hour) {
        const proj = this.map.getProjection();
        const zfactor = Math.pow(2, zoom);
        const top = proj.fromPointToLatLng(
            new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor)
        );
        const bot = proj.fromPointToLatLng(
            new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor)
        );
        const bbox = [top.lng(), bot.lat(), bot.lng(), top.lat()].join(',');

        return `http://geoserver.tuaf.edu.vn/mt_thainguyen/wms?service=WMS&version=1.1.0&request=GetMap&layers=mt_thainguyen:${factory}_${day}_${hour}&styles=&bbox=${bbox}&width=256&height=256&srs=EPSG:4326&format=image/png&transparent=true`;
    }

    addThaiNguyenBoundaries(boundaries) {
        this.clearThaiNguyenBoundaries();

        boundaries.forEach(area => {
            if (!area.geometry?.coordinates?.[0]?.[0]) {
                console.error('Invalid geometry data for area:', area);
                return;
            }

            const coordinates = area.geometry.coordinates[0][0].map(coord => ({
                lat: coord[1],
                lng: coord[0]
            }));

            const polygon = new google.maps.Polygon({
                paths: coordinates,
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.1,
                map: this.map,
                visible: true
            });

            this.setupPolygonListeners(polygon);
            this.thaiNguyenPolygons.push({
                id: area.id,
                polygon: polygon
            });
        });
    }

    clearThaiNguyenBoundaries() {
        this.thaiNguyenPolygons.forEach(item => {
            if (item.polygon) {
                item.polygon.setMap(null);
            }
        });
        this.thaiNguyenPolygons = [];
    }

    setupPolygonListeners(polygon) {
        polygon.addListener("mouseover", () => {
            polygon.setOptions({ fillOpacity: 0.3 });
        });

        polygon.addListener("mouseout", () => {
            polygon.setOptions({ fillOpacity: 0.1 });
        });

        polygon.setOptions({ clickable: false });
    }

    showCurrentLayers() {
        this.currentMode = 'current';
        
        const overlays = this.map.overlayMapTypes.getArray();
        while(overlays.length > 0) {
            this.map.overlayMapTypes.removeAt(0);
        }

        this.map.overlayMapTypes.push(this.districtBoundaryLayer);
        this.map.overlayMapTypes.push(this.communeBoundaryLayer);
        this.map.overlayMapTypes.push(this.wmsLayer);
        this.map.overlayMapTypes.push(this.laHienLayer);
        this.map.overlayMapTypes.push(this.caoNganLayer);
        this.map.overlayMapTypes.push(this.quangSonLayer);
        this.map.overlayMapTypes.push(this.quanTrieuLayer);
        this.map.overlayMapTypes.push(this.luuXaLayer);
    }

    updateForecastLayers(day, hour) {
        this.currentMode = 'forecast';
        this.currentForecastDay = day;
        this.currentForecastHour = hour;
        
        const overlays = this.map.overlayMapTypes.getArray();
        while(overlays.length > 0) {
            this.map.overlayMapTypes.removeAt(0);
        }

        // Thêm các layer cơ bản
        this.map.overlayMapTypes.push(this.districtBoundaryLayer);
        this.map.overlayMapTypes.push(this.communeBoundaryLayer);
        this.map.overlayMapTypes.push(this.wmsLayer);  // Layer AQI chung

        // Thêm các layer dự báo của nhà máy
        if (day !== "0") { // Nếu không phải ngày hiện tại
            this.factoryLayers.forEach(factory => {
                const forecastLayer = new google.maps.ImageMapType({
                    getTileUrl: (coord, zoom) => this.getForecastWMSTileUrl(
                        coord, zoom, factory, day, hour
                    ),
                    tileSize: new google.maps.Size(256, 256),
                    isPng: true,
                    opacity: 0.7,
                    name: `${factory}_forecast`
                });
                this.map.overlayMapTypes.push(forecastLayer);
            });
        } else { // Nếu là ngày hiện tại, sử dụng layer thường
            this.map.overlayMapTypes.push(this.laHienLayer);
            this.map.overlayMapTypes.push(this.caoNganLayer);
            this.map.overlayMapTypes.push(this.quangSonLayer);
            this.map.overlayMapTypes.push(this.quanTrieuLayer); 
            this.map.overlayMapTypes.push(this.luuXaLayer);
        }
    }

    refreshWMSLayers() {
        if (this.currentMode === 'current') {
            this.showCurrentLayers();
        } else {
            this.updateForecastLayers(this.currentForecastDay, this.currentForecastHour);
        }
    }
}