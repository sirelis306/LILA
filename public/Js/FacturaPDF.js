class GeneradorFacturaPDF {
    constructor() {
        this.empresa = {
            nombre: "Vibras",
            ruc: "RIF: J-123456789",
            direccion: "Calle Cualquiera 123",
            ciudad: "Ciudad, Estado 12345",
            telefono: "(000) 000-0000",
            email: "hola@sitioincreible.com",
            sitio: "www.sitioincreible.com"
        };
    }

    generarFactura(datosVenta) {
        try {
            console.log('Generando factura A4 con datos:', datosVenta);

            const { jsPDF } = window.jspdf;
    
            const doc = new jsPDF({
                unit: 'mm',
                format: 'a4' 
            });

            // --- Variables de layout para A4 ---
            const margin = 10;
            const pageWidth = doc.internal.pageSize.getWidth();
            const rightEdge = pageWidth - margin;
            const blueColor = '#2980b9';
            const whiteColor = '#FFFFFF';
            const blackColor = '#000000';
            let yPos = 15;

            // --- Helper para formato de moneda ---
            const formatCurrency = (num) => {
                const n = parseFloat(num || 0);
                if (n < 0) {
                    return `(${Math.abs(n).toFixed(2)})`;
                }
                return n.toFixed(2);
            };

            // ---Para formato de Bolívares ---
            const formatBolivares = (num) => {
                const n = parseFloat(num || 0);
                return n.toLocaleString("es-VE", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " Bs";
            };

            // --- ENCABEZADO ---
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.text(this.empresa.nombre, margin, yPos);
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.text(this.empresa.direccion, margin, yPos + 4);
            doc.text(this.empresa.ciudad, margin, yPos + 8);
            doc.text(`Teléfono: ${this.empresa.telefono}`, margin, yPos + 12);

            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('FACTURA', rightEdge, yPos, { align: 'right' });
            yPos += 6;

            const boxWidth = 40; 
            const boxHeight = 6;
            const boxX1 = rightEdge - (boxWidth * 2) - 2;
            const boxX2 = rightEdge - boxWidth;

            doc.setFillColor(blueColor);
            doc.rect(boxX1, yPos, boxWidth, boxHeight, 'F');
            doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(whiteColor);
            doc.text('FACTURA #', boxX1 + boxWidth / 2, yPos + 4, { align: 'center' });
            doc.setFontSize(9); doc.setFont('helvetica', 'normal'); doc.setTextColor(blackColor);
            
            // --- Convertir el número de factura a string ---
            const numeroFacturaString = (datosVenta.numeroFactura || '2034').toString();
            doc.text(numeroFacturaString, boxX1 + boxWidth / 2, yPos + 10, { align: 'center' });

            doc.setFillColor(blueColor);
            doc.rect(boxX2, yPos, boxWidth, boxHeight, 'F');
            doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(whiteColor);
            doc.text('FECHA', boxX2 + boxWidth / 2, yPos + 4, { align: 'center' });
            doc.setFontSize(9); doc.setFont('helvetica', 'normal'); doc.setTextColor(blackColor);
            doc.text(datosVenta.fecha || '21/02/2018', boxX2 + boxWidth / 2, yPos + 10, { align: 'center' });

            yPos += 20;

            // --- INFORMACIÓN DEL CLIENTE (Layout 2 columnas) ---
            const col1Width = (pageWidth / 2) - margin - 2; 
            const col2Width = (pageWidth / 2) - margin;     
            const col1X = margin;
            const col2X = margin + col1Width + 2;

            doc.setFillColor(blueColor);
            doc.rect(col1X, yPos, col1Width, boxHeight, 'F');
            doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(whiteColor);
            doc.text('FACTURAR A', col1X + 2, yPos + 4);

            doc.setFontSize(8); doc.setFont('helvetica', 'normal'); doc.setTextColor(blackColor);
            let yCliente = yPos + boxHeight + 4;
            doc.text(datosVenta.cliente?.nombre || '[Nombre]', col1X, yCliente, { maxWidth: col1Width });
            doc.text(datosVenta.cliente?.direccion || '[Dirección]', col1X, yCliente + 4, { maxWidth: col1Width });
            doc.text(datosVenta.cliente?.telefono || '[Teléfono]', col1X, yCliente + 8, { maxWidth: col1Width });

            doc.setFillColor(blueColor);
            doc.rect(col2X, yPos, col2Width, boxHeight, 'F');
            doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(whiteColor);
            doc.text('ID. CLIENTE', col2X + 2, yPos + 4);
            doc.text('TÉRMINOS', col2X + col2Width / 2, yPos + 4); 

            doc.setFontSize(8); doc.setFont('helvetica', 'normal'); doc.setTextColor(blackColor);
            
            // --- Convertir el ID del cliente a string ---
            const idClienteString = (datosVenta.cliente?.idCliente || '564').toString();
            doc.text(idClienteString, col2X + 2, yPos + boxHeight + 4);

            doc.text('Pagadero al recibirse', col2X + col2Width / 2, yPos + boxHeight + 4, { maxWidth: col2Width / 2 - 2 });

            yPos = yCliente + 12 + 10;

            // --- TABLA DE PRODUCTOS ---
            const headers = [['DESCRIPCIÓN', 'CANT', 'PRECIO UNITARIO', 'MONTO']];
            const body = [];

            if (datosVenta.items && datosVenta.items.length > 0) {
                datosVenta.items.forEach(item => {
                    const precio = parseFloat(item.precio || 0);
                    const cantidad = parseInt(item.cantidad || 0);
                    const subtotal = precio * cantidad;

                    body.push([
                        item.nombre || 'Producto',
                        cantidad.toString(),
                        formatCurrency(precio),
                        formatCurrency(subtotal)
                    ]);
                });
            } else {
                body.push(['No hay productos', '', '', '']);
            }

            doc.autoTable({
                startY: yPos,
                head: headers,
                body: body,
                theme: 'grid',
                margin: { left: margin, right: margin },
                styles: {
                    fontSize: 8,
                    cellPadding: 2,
                    font: 'helvetica',
                    lineWidth: 0.1,
                },
                headStyles: {
                    fillColor: blueColor,
                    textColor: whiteColor,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    textColor: blackColor,
                },
                columnStyles: {
                    0: { cellWidth: 'auto' }, 
                    1: { cellWidth: 20, halign: 'center' }, 
                    2: { cellWidth: 35, halign: 'right' }, 
                    3: { cellWidth: 35, halign: 'right' }  
                }
            });

            yPos = doc.lastAutoTable.finalY + 5;

            // ---  SECCIÓN DE TOTALES ---
            const totalCol1 = rightEdge - 60; 
            const totalCol2 = rightEdge;      

            doc.setFontSize(9); 
            doc.setFont('helvetica', 'normal');

            doc.text('Subtotal USD:', totalCol1, yPos);
            doc.text(`$${formatCurrency(datosVenta.subtotalUSD)}`, totalCol2, yPos, { align: 'right' });
            yPos += 5;

            doc.text('Subtotal BS:', totalCol1, yPos);
            doc.text(formatBolivares(datosVenta.subtotalBS), totalCol2, yPos, { align: 'right' });
            yPos += 5;

            doc.text('IVA (16%) USD:', totalCol1, yPos);
            doc.text(`$${formatCurrency(datosVenta.ivaUSD)}`, totalCol2, yPos, { align: 'right' });
            yPos += 5;
            
            yPos += 2;
            doc.setLineWidth(0.2);
            doc.line(totalCol1 - 5, yPos, rightEdge, yPos); 
            yPos += 5;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text('Total USD:', totalCol1, yPos);
            doc.text(`$${formatCurrency(datosVenta.totalUSD)}`, totalCol2, yPos, { align: 'right' });
            yPos += 6; 

            doc.text('Total BS:', totalCol1, yPos);
            doc.text(formatBolivares(datosVenta.totalBS), totalCol2, yPos, { align: 'right' });

            // --- PIE DE PÁGINA ---
            const pageHeight = doc.internal.pageSize.getHeight();
            let footerY = pageHeight - 20;

            if (yPos > footerY - 10) {
                 footerY = yPos + 20; 
            }

            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            doc.text('¡Gracias por su compra!', pageWidth / 2, footerY, { align: 'center' });
            footerY += 4;

            doc.setFont('helvetica', 'normal');
            doc.text(`Método: ${datosVenta.metodoPago || 'N/A'} | Ref: ${datosVenta.referencia || 'N/A'}`, pageWidth / 2, footerY, { align: 'center' });
            footerY += 4;

            doc.text(`${this.empresa.telefono} | ${this.empresa.email}`, pageWidth / 2, footerY, { align: 'center' });
            footerY += 4; 
            
            doc.text(this.empresa.sitio, pageWidth / 2, footerY, { align: 'center' });

            // --- Lógica del Modal  ---
            console.log('✅ Generando previsualización en modal...');
            const nombreArchivo = `Factura_${datosVenta.numeroFactura || 'temp'}.pdf`;

            // Llamamos a la función helper
            if (typeof mostrarPdfEnModal === 'function') {
                mostrarPdfEnModal(doc, nombreArchivo);
            } else {
                // Fallback por si algo falla
                console.error('Error: La función mostrarPdfEnModal no está definida.');
                alert('Error al mostrar el modal. Abriendo en nueva pestaña.');
                doc.output('dataurlnewwindow');
            }
            
            return true;


        } catch (error) {
            console.error('❌ Error generando factura PDF:', error);
            // Mostramos el error en el modal de stock/alerta
            if (typeof mostrarStockAlertModal === 'function') {
                mostrarStockAlertModal(error.message, '❌ Error de PDF');
            } else {
                alert('Error al generar la factura. Por favor, intente nuevamente.');
            }
            return false;
        }
    }
}

// Instancia global
const generadorFactura = new GeneradorFacturaPDF();