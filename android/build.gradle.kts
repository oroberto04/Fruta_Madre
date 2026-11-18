allprojects {
    repositories {
        google()
        mavenCentral()
        // Repositorio nativo requerido por el SDK oficial de Mercado Pago.
        maven {
            url = uri("https://artifacts.mercadolibre.com/repository/android-releases")
            content {
                includeGroup("com.mercadolibre.android.device")
            }
        }
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
