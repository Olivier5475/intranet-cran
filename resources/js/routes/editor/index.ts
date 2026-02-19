import folder from './folder'
import document from './document'
import file from './file'
import model from './model'
const editor = {
    folder: Object.assign(folder, folder),
document: Object.assign(document, document),
file: Object.assign(file, file),
model: Object.assign(model, model),
}

export default editor