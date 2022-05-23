
import Navigation from "./components/Navigation";


const App = () => {
    
    return (
        <>
        <Navigation />
        <Switch>
            <Route exact path="/" component={Home} />
            <Route path="/service" component={} />
            <Route path="/charts" component={} />
        </Switch>
        </>
    );
}

export default App;