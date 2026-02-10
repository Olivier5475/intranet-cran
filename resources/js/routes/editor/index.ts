import folder from './folder'
import document from './document'
import file from './file'
const editor = {
    folder: Object.assign(folder, folder),
document: Object.assign(document, document),
file: Object.assign(file, file),
}

export default editor