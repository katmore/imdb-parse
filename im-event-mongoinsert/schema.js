db.actress.ensureIndex({ihash:1},{unique:true});
db.cast.ensureIndex({ihash:1},{unique:true});
db.project.ensureIndex({ihash:1},{unique:true});
db.episode.ensureIndex({ihash:1},{unique:true});